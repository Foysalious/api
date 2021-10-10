<?php namespace Sheba\MovieTicket\Vendor\BlockBuster;

use App\Models\MovieTicketVendor;
use Exception;
use GuzzleHttp\Client;
use Sheba\MovieTicket\MovieTicketRequest;
use Sheba\MovieTicket\Response\BlockBusterResponse;
use Sheba\MovieTicket\Response\MovieResponse;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\MovieTicket\Vendor\Vendor;
use SimpleXMLElement;
use Sheba\MovieTicket\Actions;

class BlockBuster extends Vendor
{
    // User Credentials
    private $userName;
    private $secretCode;
    private $apiKey;

    // API Urls
    private $apiUrl;
    private $balanceApiUrl;
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
     * @throws Exception
     */
    public function init()
    {
        $this->userName = config('blockbuster.username');
        $this->secretCode = config('blockbuster.secret_code');
        $this->apiKey = config('blockbuster.api_key');
        $this->apiUrl = config('blockbuster.base_url');
        $this->balanceApiUrl = config('blockbuster.balance_api_base_url');
        $this->httpClient = new Client();

    }

    /**
     * @param $action
     * @return string
     * @throws Exception
     */
    public function generateURIForAction($action, $params = [])
    {
        switch ($action) {
            case Actions::GET_MOVIE_LIST:
                $api_url = $this->apiUrl . 'movie_list_running.php';
                break;
            case Actions::GET_THEATRE_LIST:
                $api_url = $this->apiUrl . 'movie_schedule.php';
                break;
            case Actions::GET_THEATRE_SEAT_STATUS:
                $api_url = $this->apiUrl . 'movie_schedule_theatre_seat_status.php';
                break;
            case Actions::REQUEST_MOVIE_TICKET_SEAT:
                $api_url = $this->apiUrl . 'movie_seat_booking_request.php';
                break;
            case Actions::UPDATE_MOVIE_SEAT_STATUS:
                $api_url = $this->apiUrl . 'movie_ticket_confirm.php';
                break;
            case Actions::GET_VENDOR_BALANCE:
                $api_url =  $this->balanceApiUrl.'balance/balance.php';
                break;
            default:
                throw new Exception('Invalid Action');
                break;
        }
        return $api_url;
    }

    private function addParamsToUrl($url, $params)
    {
        foreach ($params as $key => $value) {
            $url .= '&' . $key . '=' . $value;
        }
        return $url;
    }

    /**
     * @param MovieTicketRequest $movieTicketRequest
     * @return MovieResponse
     * @throws GuzzleException
     */
    public function buyTicket(MovieTicketRequest $movieTicketRequest): MovieResponse
    {
        $this->init();
        $blockbuster_response = new BlockBusterResponse();
        $response = $this->post(Actions::UPDATE_MOVIE_SEAT_STATUS, ['trx_id' => $movieTicketRequest->getTrxId(), 'DTMSID' => $movieTicketRequest->getDtmsId(), 'ticket_id' => $movieTicketRequest->getTicketId(), 'ConfirmStatus' => $movieTicketRequest->getConfirmStatus(),]);
        $response->place = 'Blockbuster Movies, Jamuna Future Park';
        $response->image_url = $movieTicketRequest->getImageUrl();
        $blockbuster_response->setResponse($response);
        return $blockbuster_response;
    }

    /**
     * @param $action
     * @param array $params
     * @return SimpleXMLElement
     * @throws GuzzleException
     * @throws Exception
     */
    public function get($action, $params = [])
    {
        try {
            $response = $this->httpClient->request('GET', $this->generateURIForAction($action, $params));
            $body = $response->getBody()->getContents();
            return $this->isJson($body) ? $body : $this->parse($body);
        } catch (GuzzleException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $action
     * @param array $body
     * @return mixed
     * @throws GuzzleException
     * @throws Exception
     */
    public function post($action, $body = [])
    {
        $body['username'] = $this->userName;
        if (!isset($body['trx_id'])) $body['trx_id'] = 'SHEBA' . rand(0, 32200);
        if (isset($body['MovieID'])) {
            $body['MovieID'] = $this->parseMovieIdToBlockBusterFormat($body['MovieID']);
        }
        try {
            $ch_tt = curl_init($this->generateURIForAction($action, []));
            $post_data = json_encode($body);
            $header = $this->getHeaders();
            curl_setopt($ch_tt, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch_tt, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch_tt, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_tt, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch_tt, CURLOPT_FOLLOWLOCATION, 1);
            $result_tt = curl_exec($ch_tt);
            curl_close($ch_tt);
            $result_tt = json_decode($result_tt);
            return $this->formatResponse($action, $result_tt);
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    private function getHeaders()
    {
        return array('Content-Type: application/json', 'secretecode: ' . $this->secretCode, 'apikey: ' . $this->apiKey);
    }

    /**
     * @param $action
     * @param $response
     * @return mixed|null
     * @throws Exception
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
                return $this->getTheatreSeatStatusResponse($response);
                break;
            case Actions::REQUEST_MOVIE_TICKET_SEAT:
                return $this->bookTicketResponse($response);
                break;
            case Actions::UPDATE_MOVIE_SEAT_STATUS:
                return $this->updateMovieTicketStatus($response);
                break;
            case Actions::GET_VENDOR_BALANCE:
                return $this->getVendorBalance($response);
                break;
            default:
                throw new Exception('Invalid Action');
                break;
        }
    }

    /**
     * @param $response
     * @return mixed
     * @throws Exception
     */
    private function getMovieListResponse($response)
    {
        if ($response->api_validation && $response->api_validation->status === "ok") {
            if ($response->api_response->status === "ok") {
                $movies = $response->api_response->movie_list;
                foreach ($movies as $index => $movie) {
                    $data = ["id" => (int)$movie->MovieID, "MovieID" => $movie->MovieID, "MovieName" => $movie->MovieName, "DirName" => $movie->DirName, "ReleaseDate" => $movie->ReleaseDate, "MovieStartDate" => $movie->MovieStartDate, "MovieEndDate" => $movie->MovieEndDate, "MovieType" => $movie->MovieType, "MovieStatus" => $movie->MovieStatus, "MovieTrailer" => $movie->MovieTrailer, "Status" => $movie->Status, "Banner" => $movie->Banner, "BannerSmall" => $movie->BannerSmall];
                    $movies[$index] = $data;
                }
                return $movies;
            }
            return null;
        }
        throw new Exception('Server error');
    }

    /**
     * @param $response
     * @return mixed
     * @throws Exception
     */
    private function getTheatreListResponse($response)
    {
        if ($response && $response->api_validation && $response->api_validation->status === "ok") {
            if ($response->api_response->status === "ok") {
                $schedules = $response->api_response->movie_schedule;
                foreach ($schedules as $index => $schedule) {
                    $data = ["id" => (int)$schedule->MovieID, "MovieID" => $schedule->MovieID, "MovieName" => $schedule->MovieName, "RequestDate" => $schedule->RequestDate, "DTMID" => $schedule->DTMID, "TheatreName" => $schedule->TheatreName, "ShowTime" => $schedule->ShowTime, "Slot" => $schedule->Slot];
                    $schedules[$index] = $data;
                }
                return $schedules;
            }
            return null;
        }
        throw new Exception('Server error');
    }

    /**
     * @param $response
     * @return null
     * @throws Exception
     */
    private function getTheatreSeatStatusResponse($response)
    {
        if ($response && $response->api_validation && $response->api_validation->status === "ok") {
            if ($response->api_response->status === "ok") {
                $seatStatus = $response->api_response->movie_schedule_theatre_seat_status;
                $seat_classes = explode("|", $seatStatus->SeatClass);
                $seat_classes_default = ['E_FRONT', 'E_REAR'];
                $seat_prices = explode("|", $seatStatus->SeatClassTicketPrice);

                $seats = array();
                foreach ($seat_classes_default as $index => $seat_class) {
                    $key_of_total_seats = 'Total_' . str_replace("-", "_", $seat_class) . '_Seat';
                    $key_of_available_seats = str_replace("-", "_", $seat_class) . '_Available_Seat';
                    $original_price = round((float)$seat_prices[$index], 2);
                    $total_price = $this->priceAfterShebaCommission($original_price);
                    $seat = array('class' => $seat_classes[$index], 'price' => $total_price, 'total_seats' => (int)$seatStatus->{$key_of_total_seats}, 'available_seats' => (int)$seatStatus->{$key_of_available_seats});
                    array_push($seats, $seat);
                }
                $status = array('dtmsid' => $seatStatus->DTMSID, 'dtmid' => $seatStatus->DTMID, 'seats' => $seats);
                return $status;
            } else return $response->api_response;
        }
        throw new Exception('Server error');
    }

    /**
     * @param $response
     * @return mixed
     * @throws Exception
     */
    private function bookTicketResponse($response)
    {
        if ($response && $response->api_validation && $response->api_validation->status === "ok") {
            if ($response->api_response->status === "ok") {
                $response = $response->api_response->movie_seat_booking_request;
                $response->cost = $this->priceAfterShebaCommission($response->cost);
                return $response;
            } else
                return $response->api_response;
        }
        throw new Exception('Server error');
    }

    /**
     * @param $response
     * @return
     * @throws Exception
     */
    private function updateMovieTicketStatus($response)
    {
        if ($response && $response->api_validation && $response->api_validation->status === "ok") {
            if ($response->api_response->status === "ok") return $response->api_response->ticket_confirm_status; else
                return $response->api_response;
        }
        throw new Exception('Server error');
    }

    /**
     * @param $response
     * @return mixed
     * @throws Exception
     */
    private function getVendorBalance($response)
    {
        if($response && $response->api_validation && $response->api_validation->status === "ok") {
            if($response->api_response)
                return $response->api_response->available_balance;
            else
                return $response->api_response;
        }
        throw new \Exception('Server error');

    }

    private function priceAfterShebaCommission($original_price)
    {
        $price_without_sheba_commission = round((float)$original_price, 2);
        $sheba_commission = $this->shebaCommission($price_without_sheba_commission);
        $total_price = $price_without_sheba_commission + $sheba_commission;
        $total_price = ceil($total_price);
        return $total_price;
    }

    private function shebaCommission($price)
    {
        $sheba_commission_percentage = $this->shebaCommissionPercentage();
        return $price * ($sheba_commission_percentage / 100);
    }

    private function shebaCommissionPercentage()
    {
        return MovieTicketVendor::find(1)->sheba_commission;
    }

    public function parseMovieIdToBlockBusterFormat($id)
    {
        return str_pad($id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * @return mixed
     * @throws GuzzleException
     * @throws Exception
     */
    public function balance()
    {
        try {
            $this->init();
            return $this->post(Actions::GET_VENDOR_BALANCE, []);
        } catch (GuzzleException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }


    }
}
