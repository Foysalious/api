<?php namespace Sheba\MovieTicket;

use GuzzleHttp\Exception\GuzzleException;
use Sheba\MovieTicket\Vendor\BlockBuster\BlockBuster;
use Sheba\MovieTicket\Vendor\BlockBuster\VendorManager;

class MovieTicketManager
{
    /** @var VendorManager $vendorManager **/
    private $vendorManager;
    private $movieTicket;

    public function __construct(VendorManager $vendorManager, MovieTicket $movieTicket)
    {
       $this->vendorManager = $vendorManager;
       $this->movieTicket = $movieTicket;
    }

    public function initVendor() {
        $this->vendorManager->setVendor(new BlockBuster())->initVendor();
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
                $child_parsed = $this->convertToJson($child);
                $slots = [];
                for($i = 1 ; $i<=5; $i++) {
                    $key = 'Show_0'.$i;
                    $slot = $child_parsed->{$key};
                    if($slot !== 'No-Show')
                        array_push($slots,['key' => $key, 'slot' =>$slot]);
                }
                $child_parsed->slots = $slots;
                $theatres[]=$child_parsed;
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
            $seatStatus = $this->convertToJson($seatStatus->children()[0]);
            $seat_classes = explode("|",$seatStatus->SeatClass);
            $seat_prices = explode("|",$seatStatus->SeatClassTicketPrice);
            $seats = array();
            foreach($seat_classes as $index => $seat_class) {
                $key_of_total_seats = 'Total_'.str_replace("-","_",$seat_class).'_Seat';
                $key_of_available_seats = str_replace("-","_",$seat_class).'_Available_Seat';
                $seat = array(
                    'class' => $seat_class,
                    'price' => round((float) $seat_prices[$index],2),
                    'total_seats' => (int) $seatStatus->{$key_of_total_seats},
                    'available_seats' => (int) $seatStatus->{$key_of_available_seats}
                );
                array_push($seats,$seat);
            }
            $status = array(
              'dtmsid' => $seatStatus->DTMSID,
              'dtmid' => $seatStatus->DTMID,
              'seats' => $seats
            );
            return $status;
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

    private function  convertToJson($response) {
        return json_decode(json_encode($response));
    }

}