<?php
namespace App\Library;

class PortWallet
{
    private $_appKey = "";
    private $_secretKey = "";

    public $mode = "sandbox";

    private $_liveEndpoint = "http://api.portwallet.com/api/v1/";
    private $_sandboxEndpoint = "http://api.sandbox.portwallet.com/api/v1/";

    protected $_params = array();

    function __construct($appKey=NULL, $secretKey=NULL) {

        if(!empty($appKey))
            $this->_setAppKey ($appKey);

        if(!empty($secretKey))
            $this->_setSecretKey ($secretKey);
    }

    function generateInvoice($data)
    {
        $data['call'] = "gen_invoice";
        $this->_params = $data;

        return $this->_process("post");
    }

    function ipnValidate($data)
    {
        $data['call'] = "ipn_validate";
        $this->_params = $data;

        return $this->_process("post");
    }

    public function setMode($mode="live")
    {
        $this->mode = $mode;
    }

    protected function _setAppKey($appKey)
    {
        $this->_appKey = $appKey;
    }

    protected function _setSecretKey($secretKey)
    {
        $this->_secretKey = $secretKey;
    }


    private function _process($method = "get") {
        $this->_params['timestamp'] = time();
        $this->_params['app_key'] = $this->_appKey;
        $this->_params['token'] = md5($this->_secretKey . $this->_params['timestamp']);
        return $this->_request($method);
    }

    private function _request($method) {

        $ch = curl_init();

        if($this->mode == "live") {
            curl_setopt($ch, CURLOPT_URL, $this->_liveEndpoint);
        } else {
            curl_setopt($ch, CURLOPT_URL, $this->_sandboxEndpoint);
        }

        if ($method == "post") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_params);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        //var_dump($response);die();
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] == 200 && !empty($response)) {
            return json_decode($response);
        } else
            return false;
    }


}