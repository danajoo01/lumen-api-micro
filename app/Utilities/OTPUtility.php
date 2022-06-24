<?php

namespace App\Utilities;

use Curl\Curl;

class OTPUtility
{
    private static $instance = null;

    public static function getInstance()
    {
        if(!self::$instance) {
            self::$instance = new OTPUtility;
        }

        return self::$instance;
    }

    public static function getBaseUrl($endpoint = '')
    {
        $url = 'https://sms.sahridaya.id/api/';
        return $url.$endpoint;
    }

    public static function sendSMS($phonenumber, $message = "")
    {
        $instance = self::getInstance();
        return $instance->makeRequest('apisms', [
            'message' => $message,
            'msisdn' => $phonenumber
        ]);
    }

    public static function sendOTP($phonenumber, $message = "")
    {
        $instance = self::getInstance();
        $code = $instance->generateCode();

        $message = "Hai, Hobe - ini kode rahasia untuk masuk Hobikoe. JANGAN BAGIKAN KODE INI KE SIAPAPUN, termasuk Hobikoe. Kode rahasia untuk masuk: $code";


        return $instance->makeRequest('apisms', [
            'message' => $message,
            'msisdn' => $phonenumber
        ]);
    }

    public function generateCode($length = 4)
    {
        $str = '';
        $character = array_merge(range('0', '9'));
        $max = count($character) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(4, $max);
            $str .= $character[$rand];
        }
        return $str;
    }

    public function makeRequest($endpoint, $data = [])
    {
        $default_data = [
            'username' => env('SAHRIDAYA_USERNAME'),
            'password' => env('SAHRIDAYA_PASSWORD'),
            'sender' => env('SAHRIDAYA_SENDER_ID')
        ];

        $data = array_merge($default_data, $data);
        $json_post_data = json_encode($data);
        $curl = new Curl();
        $curl->post(static::getBaseUrl($endpoint), $json_post_data);
        return $curl->response;
    }
}
