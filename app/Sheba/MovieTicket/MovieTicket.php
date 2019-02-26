<?php namespace Sheba\MovieTicket;


use GuzzleHttp\Exception\GuzzleException;
use Sheba\MovieTicket\Vendor\BlockBuster;
use Sheba\MovieTicket\Vendor\Vendor;
use Sheba\MovieTicket\Vendor\VendorManager;

class MovieTicket
{
    /** @var VendorManager $vendorManager **/
    private $vendorManager;

    public function __construct(VendorManager $vendorManager)
    {
       $this->vendorManager = $vendorManager;
    }

    public function initVendor() {
        $this->vendorManager->setVendor(new BlockBuster('dev'))->initVendor();
        return $this;
    }

    public function getAvailableTickets() {
        $availableMovies = $this->vendorManager->get(Actions::GET_MOVIE_LIST);
        $movies=[];
        foreach ($availableMovies->children() as $child){
            $child = json_decode(json_encode($child),true);
            if($child['MovieStatus'] === "1")
                $movies[]=$child;
        }
        return  $movies;
    }


    /**
     * @param $movie_id
     * @param $request_date
     * @return array
     * @throws GuzzleException
     */
    public function getAvailableTheatres($movie_id, $request_date)
    {
        try {
            $availableTheatres = $this->vendorManager->get(Actions::GET_THEATRE_LIST, ['MovieID' => $movie_id, 'RequestDate' => $request_date]);
            $theatres=[];
            foreach ($availableTheatres->children() as $child){
                $theatres[]=$child;
            }
            return  $theatres;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    /**
     * @param $dtmid
     * @param $slot
     * @return array
     * @throws GuzzleException
     */
    public function getTheatreSeatStatus($dtmid, $slot)
    {
        try {
            $seatStatus = $this->vendorManager->get(Actions::GET_THEATRE_SEAT_STATUS, ['DTMID' => $dtmid, 'slot' => $slot]);
            return  $seatStatus->children()[0];
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    /**
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function bookSeats($data = array()) {
        try {
            $bookingResponse = $this->vendorManager->get(Actions::REQUEST_MOVIE_TICKET_SEAT, $data);
            return  $bookingResponse;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    /**
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function updateMovieTicketStatus($data = array()) {
        try {
            $bookingResponse = $this->vendorManager->get(Actions::UPDATE_MOVIE_SEAT_STATUS, $data);
            return  $bookingResponse;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }
}