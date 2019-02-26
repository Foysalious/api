<?php namespace Sheba\MovieTicket\Vendor;


use Sheba\MovieTicket\Actions;
use Sheba\MovieTicket\MovieTicketRequest;
use Sheba\MovieTicket\Response\MovieResponse;
use Sheba\MovieTicket\TransactionGenerator;
use Sheba\MovieTicket\Vendor\BlockBuster\KeyEncryptor;

class BlockBuster extends Vendor
{
    // User Credentials
    private $userName;
    private $password;
    private $key;

    // API Urls
    private $apiUrl;
    private $imageServerUrl;
    private $secretKey;
    private $connectionMode;

    /**
     * BlockBuster constructor.
     * @param $connection_mode
     */
    public function __construct($connection_mode)
    {
        $this->imageServerUrl = config('blockbuster.image_server_url');
        $this->connectionMode = $connection_mode;
    }

    private function getSecretKey($params = [])
    {
        $cur_random_value = (new TransactionGenerator())->generate();
        $string = "password=$this->password";
        if(!isset($params['trx_id'])) $string .= "&trxid=$cur_random_value";
        $string = $this->addParamsToUrl($string, $params);
        $string .= '&format=xml';
        $BBC_Codero_Key_Generate = (new KeyEncryptor())->encrypt_cbc($string,$this->key);
        $BBC_Request_KEY_VALUE =urlencode($BBC_Codero_Key_Generate);
        return $BBC_Request_KEY_VALUE;
    }

    /**
     * @throws \Exception
     */
    public function init()
    {
        if($this->connectionMode === 'dev') {
            // Connect to dev server with test credentials
            $this->userName = config('blockbuster.username_dev');
            $this->password = config('blockbuster.password_dev');
            $this->key = config('blockbuster.key_dev');
            $this->apiUrl = config('blockbuster.test_api_url');

        } else if($this->connectionMode === 'production'){
            // Connect to live server with prod credentials
            $this->userName = config('blockbuster.username_live');
            $this->password = config('blockbuster.password_live');
            $this->key = config('blockbuster.key_live');
            $this->apiUrl = config('blockbuster.live_api_url');

        } else {
            throw new \Exception('Invalid connection mode');
        }
        $this->secretKey = $this->getSecretKey();
    }

    /**
     * @param $action
     * @return string
     * @throws \Exception
     */
    public function generateURIForAction($action, $params = [])
    {
        $this->secretKey = $this->getSecretKey($params);
        switch ($action) {
            case Actions::GET_MOVIE_LIST:
                $api_url = $this->apiUrl.'/MovieList.php?username='.$this->userName.'&request_id='.$this->secretKey;
                break;
            case Actions::GET_THEATRE_LIST:
                $api_url =  $this->apiUrl.'MovieSchedule.php?username='.$this->userName.'&request_id='.$this->secretKey;
                break;
            case Actions::GET_THEATRE_SEAT_STATUS:
                $api_url =  $this->apiUrl.'MovieScheduleTheatreSeatStatus.php?username='.$this->userName.'&request_id='.$this->secretKey;
                break;
            case Actions::REQUEST_MOVIE_TICKET_SEAT:
                $api_url =  $this->apiUrl.'MovieSeatBookingRequest.php?username='.$this->userName.'&request_id='.$this->secretKey;
                break;
            case Actions::UPDATE_MOVIE_SEAT_STATUS:
                $api_url =  $this->apiUrl.'MovieSeatUpdateStatus.php?username='.$this->userName.'&request_id='.$this->secretKey;
                break;
            default:
                throw new \Exception('Invalid Action');
                break;
        }
//        dd((new KeyEncryptor())->decrypt_cbc($this->secretKey, $this->key));
        return $api_url;
    }

    private function addParamsToUrl($url, $params)
    {
        foreach ($params as $key => $value) {
            $url .='&'.$key.'='.$value;
        }
        return $url;
    }

    function buyTicket(MovieTicketRequest $movieTicketRequest): MovieResponse
    {

    }
}