<?php
/**********************************************************\
|                                                          |
|                          hprose                          |
|                                                          |
| Official WebSite: http://www.hprose.com/                 |
|                   http://www.hprose.org/                 |
|                                                          |
\**********************************************************/

/**********************************************************\
 *                                                        *
 * Hprose/Socket/Transporter.php                          *
 *                                                        *
 * hprose socket Transporter class for php 5.3+           *
 *                                                        *
 * LastModified: Jul 28, 2016                             *
 * Author: Ma Bingyao <andot@hprose.com>                  *
 *                                                        *
\**********************************************************/

namespace Hprose\Socket;

use stdClass;
use Exception;
use ErrorException;
use Hprose\Future;
use Hprose\TimeoutException;

abstract class Transporter {
    private $client;
    private $requests = array();
    private $timeouts = array();
    private $deadlines = array();
    private $results = array();
    private $stream = null;
    private $async;
    protected abstract function appendHeader($request);
    protected abstract function createRequest($index, $request);
    protected abstract function afterWrite($request, $stream, $o);
    protected abstract function getBodyLength($stream);
    protected abstract function asyncReadError($o, $stream, $index);
    protected abstract function getResponse($stream, $o);
    protected abstract function afterRead($stream, $o, $response);

    public function __construct(Client $client, $async) {
        $this->client = $client;
        $this->async = $async;
    }
    public function __destruct() {
        if ($this->stream !== null) @fclose($this->stream);
    }
    protected function getLastError($error) {
        $e = error_get_last();
        if ($e === null) {
            return new Exception($error);
        }
        else {
            return new ErrorException($e['message'], 0, $e['type'], $e['file'], $e['line']);
        }
    }
    protected function removeStream($stream, &$pool) {
        $index = array_search($stream, $pool, true);
        if ($index !== false) {
            unset($pool[$index]);
        }
    }
    protected function readHeader($stream, $n) {
        $header = '';
        do {
            $buffer = @fread($stream, $n - strlen($header));
            $header .= $buffer;
        } while (($buffer !== false) && (strlen($header) < $n));
        if ($buffer === false) {
            return false;
        }
        return $header;
    }
    protected function asyncWrite($stream, $o) {
        $stream_id = (integer)$stream;
        if (isset($o->requests[$stream_id])) {
            $request = $o->requests[$stream_id];
        }
        else {
            if ($o->current < $o->count) {
                $request = $this->createRequest($o->current, $o->buffers[$o->current]);
                $o->requests[$stream_id] = $request;
                unset($o->buffers[$o->current]);
                $o->current++;
            }
            else {
                $this->removeStream($stream, $o->writepool);
                return;
            }
        }
        $sent = @fwrite($stream, $request->buffer, $request->length);
        if ($sent === false) {
            $o->results[$request->index]->reject($this->getLastError('request write error'));
            $this->free($o, $request->index);
        }
        if ($sent < $request->length) {
            $request->buffer = substr($request->buffer, $sent);
            $request->length -= $sent;
        }
        else {
            $this->afterWrite($request, $stream, $o);
        }
    }
    private function free($o, $index) {
        unset($o->results[$index]);
        unset($o->timeouts[$index]);
        unset($o->deadlines[$index]);
        unset($o->buffers[$index]);
    }
    private function asyncRead($stream, $o) {
        $response = $this->getResponse($stream, $o);
        if ($response === false) {
            $this->asyncReadError($o, $stream, $response->index);
            return;
        }
        $remaining = $response->length - strlen($response->buffer);
        $buffer = @fread($stream, $remaining);
        if ($buffer === false) {
            $this->asyncReadError($o, $stream, $response->index);
            return;
        }
        $response->buffer .= $buffer;
        if (strlen($response->buffer) === $response->length) {
            $result = $o->results[$response->index];
            $this->free($o, $response->index);
            $stream_id = (integer)$stream;
            unset($o->responses[$stream_id]);
            $this->afterRead($stream, $o, $response);
            $result->resolve($response->buffer);
        }
    }
    private function checkTimeout($o) {
        foreach ($o->deadlines as $index => $deadline) {
            if (microtime(true) > $deadline) {
                $o->results[$index]->reject(new TimeoutException("timeout"));
                $this->free($o, $index);
            }
        }
    }
    private function createPool($client, $o) {
        $n = min(count($o->results), $client->maxPoolSize);
        $pool = array();
        $errno = 0;
        $errstr = '';
        $context = @stream_context_create($client->options);
        for ($i = 0; $i < $n; $i++) {
            $scheme = parse_url($client->uri, PHP_URL_SCHEME);
            if ($scheme == 'unix') {
                $stream = @fsockopen('unix://' . parse_url($client->uri, PHP_URL_PATH));
            }
            else {
                $stream = @stream_socket_client(
                    $client->uri . '/' . $i,
                    $errno,
                    $errstr,
                    $o->timeouts[$i],
                    STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT,
                    $context
                );
            }
            if (($stream !== false) &&
                (@stream_set_blocking($stream, false) !== false)) {
                @stream_set_read_buffer($stream, $client->readBuffer);
                @stream_set_write_buffer($stream, $client->writeBuffer);
                $pool[] = $stream;
            }
        }
        if (empty($pool)) {
            $e = new Exception($errstr, $errno);
            foreach ($o->results as $result) {
                $result->reject($e);
            }
            $o->results = array();
            return false;
        }
        return $pool;
    }
    public function loop() {
        $client = $this->client;
        while (count($this->results) > 0) {
            $this->checkTimeout($this);
            $pool = $this->createPool($client, $this);
            if ($pool === false) continue;
            $o = new stdClass();
            $o->current = 0;
            $o->count = count($this->results);
            $o->responses = array();
            $o->requests = array();
            $o->readpool = array();
            $o->writepool = $pool;
            $o->buffers = $this->buffers;
            $o->timeouts = $this->timeouts;
            $o->deadlines = $this->deadlines;
            $o->results = $this->results;
            $this->buffers = array();
            $this->timeouts = array();
            $this->deadlines = array();
            $this->results = array();
            while (count($o->results) > 0) {
                $this->checkTimeout($o);
                $read = array_values($o->readpool);
                $write = array_values($o->writepool);
                $except = null;
                $timeout = max(0, min($o->deadlines) - microtime(true));
                $tv_sec = floor($timeout);
                $tv_usec = ($timeout - $tv_sec) * 1000;
                $n = @stream_select($read, $write, $except, $tv_sec, $tv_usec);
                if ($n === false) {
                    $e = $this->getLastError('unkown io error.');
                    foreach ($o->results as $result) {
                        $result->reject($e);
                    }
                    $o->results = array();
                }
                if ($n > 0) {
                    foreach ($write as $stream) $this->asyncWrite($stream, $o);
                    foreach ($read as $stream) $this->asyncRead($stream, $o);
                }
            }
        }
    }
    public function asyncSendAndReceive($buffer, stdClass $context) {
        $timeout = ($context->timeout / 1000);
        $deadline = microtime(true) + $timeout;
        $result = new Future();
        $this->buffers[] = $buffer;
        $this->timeouts[] = $timeout;
        $this->deadlines[] = $deadline;
        $this->results[] = $result;
        return $result;
    }
    private function write($stream, $request) {
        $buffer = $this->appendHeader($request);
        $length = strlen($buffer);
        while (true) {
            $sent = @fwrite($stream, $buffer, $length);
            if ($sent === false) {
                return false;
            }
            if ($sent < $length) {
                $buffer = substr($buffer, $sent);
                $length -= $sent;
            }
            else {
                return true;
            }
        }
    }
    private function read($stream) {
        $length = $this->getBodyLength($stream);
        if ($length === false) return false;
        $response = '';
        while (($remaining = $length - strlen($response)) > 0) {
            $buffer = @fread($stream, $remaining);
            if ($buffer === false) {
                return false;
            }
            $response .= $buffer;
        }
        return $response;
    }
    public function syncSendAndReceive($buffer, stdClass $context) {
        $client = $this->client;
        $timeout = ($context->timeout / 1000);
        $sec = floor($timeout);
        $usec = ($timeout - $sec) * 1000;
        $trycount = 0;
        $errno = 0;
        $errstr = '';
        while ($trycount <= 1) {
            if ($this->stream === null) {
                $this->stream = @stream_socket_client(
                    $this->client->uri,
                    $errno,
                    $errstr,
                    $timeout,
                    STREAM_CLIENT_CONNECT,
                    stream_context_create($client->options));
                if ($this->stream === false) {
                    throw new Exception($errstr, $errno);
                }
            }
            $stream = $this->stream;
            @stream_set_read_buffer($stream, $client->readBuffer);
            @stream_set_write_buffer($stream, $client->writeBuffer);
            if (@stream_set_timeout($stream, $sec, $usec) == false) {
                if ($trycount > 0) {
                    throw $this->getLastError("unknown error");
                }
                $trycount++;
            }
            else {
                break;
            }
        }
        if ($this->write($stream, $buffer) === false) {
            throw $this->getLastError("request write error");
        }
        $response = $this->read($stream, $buffer);
        if ($response === false) {
            throw $this->getLastError("response read error");
        }
        return $response;
    }
    public function sendAndReceive($buffer, stdClass $context) {
        if ($this->async) {
            return $this->asyncSendAndReceive($buffer, $context);
        }
        return $this->syncSendAndReceive($buffer, $context);
    }
}
