<?php
/**
 * curl Class
 *
 * class inferface of phpredis extension
 *
 * @version:  1.0
 * @author:   NULL
 * @license:  LGPL
 *
 */
class Curl
{

    private $headers;
    private $user_agent;
    private $compression;
    private $cookie_file;
    private $proxy;
    private $host;

    /**
     * construct class
     */
    function __construct($host)
    {
        $this->host = $host;
    }

    function cURL($cookies=TRUE,$cookie='cookies.txt',$compression='gzip',$proxy='') { 
        $this->headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg'; 
        $this->headers[] = 'Connection: Keep-Alive'; 
        $this->headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8'; 
        $this->user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)'; 
        $this->compression = $compression; 
        $this->proxy = $proxy; 
        $this->cookies = $cookies;
        if ($this->cookies == TRUE) {
            $this->cookie($cookie);
        } 
    }

    

    // Get请求
    function get($url) { 
        $ch = curl_init($url); 
        @curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers); 
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent); 
        if ($this->cookies == TRUE) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file); 
        }
        if ($this->cookies == TRUE) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file); 
        }
        curl_setopt($ch,CURLOPT_ENCODING , $this->compression); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy); 
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        $result = curl_exec($ch); 
        curl_close($ch); 
        return $result; 
    }

    function post($url,$data) {
        $ch = curl_init($url); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers); 
        curl_setopt($ch, CURLOPT_HEADER, 1); 
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent); 
        if ($this->cookies == TRUE) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file); 
        }
        if ($this->cookies == TRUE) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file); 
        }
        curl_setopt($ch, CURLOPT_ENCODING , $this->compression); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy); 
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt($ch, CURLOPT_POST, 1); 
        $result = curl_exec($ch); 
        curl_close($ch);
        return $result; 
    } 


    public  function http_curl($url = ""){
        $ch = curl_init();

        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //执行并获取HTML文档内容
        $output = curl_exec($ch);

        //释放curl句柄
        curl_close($ch);

        return $output;
    }



    public  function blowfishByArray($data = array()){


        $blowfish = new Blowfish();
  
        $res_str = $blowfish->encrypt(http_build_query($data));

        $url_str = $this->host.$res_str;
        return $url_str;
    }







}
