<?php namespace Sheba\MovieTicket\Vendor;


use Sheba\MovieTicket\TransactionGenerator;
use Sheba\MovieTicket\Vendor\BlockBuster\KeyEncryptor;

class BlockBuster
{
    // User Credentials
    private $userName;
    private $password;
    private $key;

    // API Urls
    private $apiUrl;
    private $imageServerUrl;
    private $secret_key;

    /**
     * BlockBuster constructor.
     * @param $connection_mode
     * @throws \Exception
     */
    public function __construct($connection_mode)
    {
        $this->imageServerUrl = config('blockbuster.image_server_url');
        if($connection_mode === 'dev') {
            // Connect to dev server with test credentials
            $this->userName = config('blockbuster.username_dev');
            $this->password = config('blockbuster.password_dev');
            $this->key = config('blockbuster.key_dev');
            $this->apiUrl = config('blockbuster.test_api_url');

        } else if($connection_mode === 'production'){
            // Connect to live server with prod credentials
            $this->userName = config('blockbuster.username_live');
            $this->password = config('blockbuster.password_live');
            $this->key = config('blockbuster.key_live');
            $this->apiUrl = config('blockbuster.live_api_url');

        } else {
            throw new \Exception('Invalid connection mode');
        }

        $this->secret_key = $this->getSercretKey();
    }

    private function getSercretKey()
    {
        $cur_random_value = (new TransactionGenerator())->generate();$string = "password=$this->password&trxid=$cur_random_value&format=xml";
        $BBC_Codero_Key_Generate = (new KeyEncryptor())->encrypt_cbc($string,$this->key);
        $BBC_Request_KEY_VALUE =urlencode($BBC_Codero_Key_Generate);
        return $BBC_Request_KEY_VALUE;
    }
}