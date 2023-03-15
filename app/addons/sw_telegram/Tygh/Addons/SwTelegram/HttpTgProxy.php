<?php

namespace Tygh\Addons\SwTelegram;

use Tygh\Http;

class HttpTgProxy extends Http {
    
    public static $_headersTg = '';
    
    public static function sendMessageProxy($addon_settings, $url, $data, $extra = array())
    {
        $method = 'GET';
        
         list($url, $data) = self::_prepareDataTg('GET', $url, $data);
        
        if (!empty($extra['timeout'])) {
            $extra['connection_timeout'] = $extra['timeout'];
        }

        $extra['connection_timeout'] = isset($extra['connection_timeout']) ? (int) $extra['connection_timeout'] : self::$default_connection_timeout;
        $extra['execution_timeout'] = isset($extra['execution_timeout']) ? (int) $extra['execution_timeout'] : self::$default_execution_timeout;
        
        $ch = curl_init();

        if (!empty($extra['basic_auth'])) {
            curl_setopt($ch, CURLOPT_USERPWD, implode(':', $extra['basic_auth']));
        }
        if (!empty($extra['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $extra['referer']);
        }
        if (!empty($extra['ssl_cert'])) {
            curl_setopt($ch, CURLOPT_SSLCERT, $extra['ssl_cert']);
            if (!empty($extra['ssl_key'])) {
                curl_setopt($ch, CURLOPT_SSLKEY, $extra['ssl_key']);
            }
        }
        if (!empty($extra['encoding'])) {
            curl_setopt($ch, CURLOPT_ENCODING , $extra['encoding']);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $extra['connection_timeout']);

        if (!empty($extra['execution_timeout'])) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $extra['execution_timeout']);
        }
        if (!empty($extra['headers'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $extra['headers']);
        }
        if (!empty($extra['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $extra['cookies']));
        }
        if (!empty($extra['binary_transfer'])) {
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        }

        if ($method == self::GET) {
            curl_setopt($ch, CURLOPT_HTTPGET, 1);

            if (!empty($data)) {
                $url .= '?' . $data;
            }

        } elseif ($method == self::POST) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        //if (self::$_curl_followlocation_support) {
        //    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //}

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (!empty($extra['write_to_file'])) {
            $f = fopen($extra['write_to_file'], 'w');
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FILE, $f);
        }

        if (!empty($addon_settings['proxy_host'])) {
            curl_setopt($ch, CURLOPT_PROXY, $addon_settings['proxy_host'] . ':' . (empty($addon_settings['proxy_port']) ? 3128 : $addon_settings['proxy_port']));
            if (!empty($addon_settings['proxy_user'])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $addon_settings['proxy_user'] . (empty($addon_settings['proxy_password']) ? '' : ':' . $addon_settings['proxy_password']));
            }
        }

        if (!empty($extra['return_handler'])) {
            return $ch;
        }

        $content = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if (!empty($content)) {
            $content = self::_parseContentTG($content);
            //$content = self::_processHeadersRedirect($method, $url, $extra, $content);
        }

        if (!empty($error)) {
            self::_setError('curl', $error, $errno);
        }

        if (!empty($extra['write_to_file'])) {
            fclose($f);
        }

        return $content;
    }
    
    public static function _parseContentTG($content)
    {
        while (strpos(ltrim($content), 'HTTP/') === 0) {
            list(self::$_headersTg, $content) = preg_split("/(\r?\n){2}/", $content, 2);
        }

        return $content;
    }
    
    public static function _prepareDataTg($method, $url, $data)
    {
        $components = parse_url($url);

        $upass = '';
        if (!empty($components['user'])) {
            $upass = $components['user'] . (!empty($components['pass']) ? ':' . $components['pass'] : '') . '@';
        }

        if (empty($components['path'])) {
            $components['path'] = '/';
        }

        $port = empty($components['port']) ? '' : (':' . $components['port']);

        $url = $components['scheme'] . '://' . $upass . $components['host'] . $port . $components['path'];

        if (!empty($components['query'])) {
            if ($method == self::GET) {
                parse_str($components['query'], $args);

                if (!empty($data) && !is_array($data) && !empty($args)) {
                    throw new DeveloperException('Http: incompatible data type passed');
                }

                $data = fn_array_merge($args, $data);
            } else {
                $url .= '?' . $components['query'];
            }
        }

        return array($url, is_array($data) ? http_build_query($data) : $data);
    }

}

