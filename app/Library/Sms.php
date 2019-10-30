<?php namespace App\Library;

class Sms
{
    public static function send_single_message($mobile, $message)
    {
        $user = env('SMS_USER');
        $pass = env('SMS_PASSWORD');
        $sid = env('SMS_SID');
        $url = "http://sms.sslwireless.com/pushapi/dynamic/server.php";
        $param = "user=$user&pass=$pass&sms[0][0]= $mobile &sms[0][1]=" . urlencode($message) . "&sms[1][2]=123456790&sid=$sid";
        $crl = curl_init();
        curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($crl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($crl, CURLOPT_URL, $url);
        curl_setopt($crl, CURLOPT_HEADER, 0);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($crl, CURLOPT_POST, 1);
        curl_setopt($crl, CURLOPT_POSTFIELDS, $param);
        $response = curl_exec($crl);
        curl_close($crl);
        #Log::info(json_encode($response));
        return $response;

    }

}
