<?php namespace Sheba\MovieTicket\Vendor\BlockBuster;

use GuzzleHttp\Client;
use Sheba\MovieTicket\Actions;
use Sheba\MovieTicket\Response\BlockBusterResponse;
use Sheba\MovieTicket\Response\MovieResponse;
use Sheba\MovieTicket\TransactionGenerator;
use Sheba\MovieTicket\Vendor\BlockBuster\KeyEncryptor;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\MovieTicket\Vendor\Vendor;

class BlockBuster extends Vendor
{
    // User Credentials
    private $userName;
    private $secretCode;
    private $apiKey;

    // API Urls
    private $apiUrl;
    private $imageServerUrl;

    private $httpClient;

    /**
     * @return mixed
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param mixed $httpClient
     */
    public function setHttpClient($httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @throws \Exception
     */
    public function init()
    {
        $this->userName = config('blockbuster.username');
        $this->secretCode = config('blockbuster.secret_code');
        $this->apiKey = config('blockbuster.api_key');
        $this->apiUrl = config('blockbuster.base_url');
        $this->httpClient = new Client();

    }

    /**
     * @param $action
     * @return string
     * @throws \Exception
     */
    public function generateURIForAction($action, $params = [])
    {
        switch ($action) {
            case Actions::GET_MOVIE_LIST:
                $api_url = $this->apiUrl.'movie_list_running.php';
                break;
            case Actions::GET_THEATRE_LIST:
                $api_url =  $this->apiUrl.'movie_schedule.php';
                break;
            case Actions::GET_THEATRE_SEAT_STATUS:
                $api_url =  $this->apiUrl.'movie_schedule_theatre_seat_status.php';
                break;
            case Actions::REQUEST_MOVIE_TICKET_SEAT:
                $api_url =  $this->apiUrl.'movie_seat_booking_request.php';
                break;
            case Actions::UPDATE_MOVIE_SEAT_STATUS:
                $api_url =  $this->apiUrl.'movie_ticket_confirm.php';
                break;
            default:
                throw new \Exception('Invalid Action');
                break;
        }
        return $api_url;
    }

    private function addParamsToUrl($url, $params)
    {
        foreach ($params as $key => $value) {
            $url .='&'.$key.'='.$value;
        }
        return $url;
    }

    function buyTicket($response): MovieResponse
    {
        $blockbuster_response = new BlockBusterResponse();
        $blockbuster_response->setResponse($response);
        return $blockbuster_response;
    }

    /**
     * @param $action
     * @param array $params
     * @return \SimpleXMLElement
     * @throws GuzzleException
     * @throws \Exception
     */
    public function get($action, $params = [])
    {
        try {
            $response = $this->httpClient->request('GET', $this->generateURIForAction($action, $params));
            $body = $response->getBody()->getContents();
            return $this->isJson($body) ? $body :$this->parse($body);
        } catch (GuzzleException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * @param $action
     * @param array $body
     * @return mixed
     * @throws GuzzleException
     * @throws \Exception
     */
    public function post($action, $body = [])
    {
        $body['username'] = $this->userName;
        if(!isset($body['trx_id']))
            $body['trx_id'] = 'SHEBA'.rand(0,32200);
        try {
            $ch_tt = curl_init($this->generateURIForAction($action,[]));
            $post_data = json_encode($body);
            $header = $this->getHeaders();
            curl_setopt($ch_tt, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch_tt, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch_tt, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_tt, CURLOPT_POSTFIELDS,$post_data);
            curl_setopt($ch_tt, CURLOPT_FOLLOWLOCATION, 1);
            $result_tt = curl_exec($ch_tt);
            curl_close($ch_tt);
            $result_tt=json_decode($result_tt);
            return $this->formatResponse($action, $result_tt);
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    private function getHeaders()
    {
        return array(
            'Content-Type: application/json',
            'secretecode: '.$this->secretCode,
            'apikey: '.$this->apiKey
        );
    }

    /**
     * @param $action
     * @param $response
     * @return mixed|null
     * @throws \Exception
     */
    private function formatResponse($action, $response)
    {
        switch ($action) {
            case Actions::GET_MOVIE_LIST:
                return $this->getMovieListResponse($response);
                break;
            case Actions::GET_THEATRE_LIST:
                return $this->getTheatreListResponse($response);
                break;
            case Actions::GET_THEATRE_SEAT_STATUS:
                return$this->getTheatreSeatStatusResponse($response);
                break;
            case Actions::REQUEST_MOVIE_TICKET_SEAT:
                return$this->bookTicketResponse($response);
                break;
            case Actions::UPDATE_MOVIE_SEAT_STATUS:
                return $this->updateMovieTicketStatus($response);
                break;
            default:
                throw new \Exception('Invalid Action');
                break;
        }
    }

    /**
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    private function getMovieListResponse($response)
    {
        if($response->api_validation && $response->api_validation->status==="ok") {
            if($response->api_response->status === "ok")
                return $response->api_response->movie_list;
            return null;
        }
        throw new \Exception('Server error');
    }

    /**
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    private function getTheatreListResponse($response)
    {
        if($response && $response->api_validation && $response->api_validation->status === "ok"){
            if($response->api_response->status === "ok")
                return $response->api_response->movie_schedule;
            return null;
        }
        throw new \Exception('Server error');
    }

    /**
     * @param $response
     * @return null
     * @throws \Exception
     */
    private function getTheatreSeatStatusResponse($response)
    {
        if($response && $response->api_validation && $response->api_validation->status === "ok"){
            if($response->api_response->status === "ok")
                return $response->api_response->movie_schedule_theatre_seat_status;
            else return $response->api_response;
        }
        throw new \Exception('Server error');
    }

    /**
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    private function bookTicketResponse($response)
    {
        if($response && $response->api_validation && $response->api_validation->status === "ok"){
            if($response->api_response->status === "ok")
                return $response->api_response->movie_seat_booking_request;
            else
                return $response->api_response;
        }
        throw new \Exception('Server error');
    }

    /**
     * @param $response
     * @throws \Exception
     */
    private function updateMovieTicketStatus($response)
    {
        if($response && $response->api_validation && $response->api_validation->status === "ok") {
            if($response->api_response->status === "ok")
                return $response->api_response->ticket_confirm_status;
            else
                return $response->api_response;
        }
        throw new \Exception('Server error');
    }
}