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
        $availableMovies = $this->vendorManager->post(Actions::GET_MOVIE_LIST);
        return  $availableMovies;
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
            $availableTheatres = $this->vendorManager->post(Actions::GET_THEATRE_LIST, ['MovieID' => $movie_id, 'ShowDate' => $request_date]);
            return  $availableTheatres;
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
            $seatStatus = $this->vendorManager->post(Actions::GET_THEATRE_SEAT_STATUS, ['DTMID' => $dtmid, 'Slot' => $slot]);
            $seat_classes = explode("|",$seatStatus->SeatClass);
            $seat_classes_default = ['E_FRONT','E_REAR'];
            $seat_prices = explode("|",$seatStatus->SeatClassTicketPrice);
            $seats = array();
            foreach($seat_classes_default as $index => $seat_class) {
                $key_of_total_seats = 'Total_'.str_replace("-","_",$seat_class).'_Seat';
                $key_of_available_seats = str_replace("-","_",$seat_class).'_Available_Seat';
                $seat = array(
                    'class' => $seat_classes[$index],
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
            $bookingResponse = $this->vendorManager->post(Actions::REQUEST_MOVIE_TICKET_SEAT, $data);
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
            $bookingResponse = $this->vendorManager->post(Actions::UPDATE_MOVIE_SEAT_STATUS, $data);
            return  $bookingResponse;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    private function  convertToJson($response) {
        return json_decode(json_encode($response));
    }

}