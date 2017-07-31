<?php
class StringUtils{
	public static function decode_seo_parameters($string) {
		$parameters = array();

		if (!$string) {
            return $parameters;
        }
        $list = preg_split('/QQ/', $string, -1, PREG_SPLIT_NO_EMPTY);
        foreach($list as $item) {
            @list($name, $value) = preg_split('/Z/', $item, 2, PREG_SPLIT_NO_EMPTY);
            if (!$name) {
                continue;
            }
            $name = urldecode($name);
            $value = urldecode($value);
            if (!isset($parameters[$name])) {
                $parameters[$name] = $value;
            } elseif (is_array($parameters[$name])) {
                $parameters[$name][] = $value;
            } else {
                $parameters[$name] = array($parameters[$name], $value);
            }
        }

        return $parameters;
	}
	public static function encode_seo_parameters($parameters) {
		$string = "";
        ksort( $parameters );
        foreach($parameters as $name=>$value) {
            if (is_array($value)) {
                ksort( $value );
                foreach ($value as $v) {
                    $string .= "QQ" . urlencode($name) . "Z" . urlencode($v);
                }
            } else {
                $string .= "QQ" . urlencode($name) . "Z" . urlencode($value);
            }
        }
        return $string;
	}

	public static function cutstr($string, $length, $dot = ' ...') {

        $charset = "utf-8";
        if(strlen($string) <= $length) {
            return $string;
        }
    
        $strcut = '';
        if(strtolower($charset) == 'utf-8') {
    
            $n = $tn = $noc = 0;
            while ($n < strlen($string)) {
    
                $t = ord($string[$n]);
                if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1; $n++; $noc++;
                } elseif(194 <= $t && $t <= 223) {
                    $tn = 2; $n += 2; $noc += 2;
                } elseif(224 <= $t && $t < 239) {
                    $tn = 3; $n += 3; $noc += 2;
                } elseif(240 <= $t && $t <= 247) {
                    $tn = 4; $n += 4; $noc += 2;
                } elseif(248 <= $t && $t <= 251) {
                    $tn = 5; $n += 5; $noc += 2;
                } elseif($t == 252 || $t == 253) {
                    $tn = 6; $n += 6; $noc += 2;
                } else {
                    $n++;
                }
    
                if ($noc >= $length) {
                    break;
                }
    
            }
            if ($noc > $length) {
                $n -= $tn;
            }
    
            $strcut = substr($string, 0, $n);
    
        } else {
            for($i = 0; $i < $length - 3; $i++) {
                $strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
            }
        }
    
        return (strlen($strcut)< strlen($string))?$strcut.$dot:$strcut;
    }

	public static function is_utf8($string) {
		// From http://w3.org/International/questions/qa-forms-utf-8.html
		return preg_match('%^(?:
		[\x09\x0A\x0D\x20-\x7E] # ASCII
		| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
		| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
		| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
		| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
		| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
		)*$%xs', $string);

	}

	public static function bin_is_hit( $value , $pos ){
		return substr( $value >> $pos , -1 ) == 1;
	}

	public static function hit_array( $value ){
		$string = strrev( decbin( $value ) );
		$hits_pos = array();
		for( $i = 0 ; $i < strlen( $string ) ; $i ++ ){
			if( $string[$i] == 1 )
				$hits_pos[] = $i;
		}
		return $hits_pos;
	}
}
