<?php
namespace Tygh\Payments\Processors;


class PSBankLib
{
    static $version = "1.0.0";

    static function calcHash($data, $key) {
        $params = array(
                'PAYMENT'           => array(
                    "AMOUNT", "CURRENCY", "ORDER", "MERCH_NAME", "MERCHANT", "TERMINAL", "EMAIL", "TRTYPE", "TIMESTAMP", "NONCE", "BACKREF"
                    ),
                'PAYMENT_NOTIFY'    => array(
                    "AMOUNT", "CURRENCY", "ORDER", "MERCH_NAME", "MERCHANT", "TERMINAL", "EMAIL", "TRTYPE", "TIMESTAMP", "NONCE", "BACKREF", "RESULT", "RC", "RCTEXT", "AUTHCODE", "RRN", "INT_REF"
                    ),
                'ACTION'            => array(
                    "ORDER", "AMOUNT", "CURRENCY", "ORG_AMOUNT", "RRN", "INT_REF", "TRTYPE", "TERMINAL", "BACKREF", "EMAIL", "TIMESTAMP", "NONCE"
                    ),
                'ACTION_NOTIFY'     => array(
                    "ORDER", "AMOUNT", "CURRENCY", "ORG_AMOUNT", "RRN", "INT_REF", "TRTYPE", "TERMINAL", "BACKREF", "EMAIL", "TIMESTAMP", "NONCE", "RESULT", "RC", "RCTEXT"
                    ),
            );
        $sign = '';
        if(in_array($data['TRTYPE'],array(1,12))) {
            $type = 'PAYMENT';
        } else {
            $type = 'ACTION';
        }
        if(isset($data['RESULT'])) {
            $type .= '_NOTIFY';
        }
        foreach($params[$type] as $param) {
            $value = $data[$param];
            if($value == "") {
                $sign .= "-";
            } else {
                $sign .= mb_strlen($value,"8bit").$value;
            }
        }
        $hash = hash_hmac("sha1", $sign, pack("H*", $key));
        return strtoupper($hash);
    }

    static function getUrl($test_mode) {
        if($test_mode) {
            $url = 'https://test.3ds.payment.ru/cgi-bin/cgi_link';
        } else {
            $url = 'https://3ds.payment.ru/cgi-bin/cgi_link';
        }
        return $url;
    }


    static function request($data, $test_mode) {
        $url = self::getUrl($test_mode);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERAGENT, 'PSBankLib '.self::$version);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
