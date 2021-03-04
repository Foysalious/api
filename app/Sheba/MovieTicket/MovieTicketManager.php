<?php namespace Sheba\MovieTicket;

use GuzzleHttp\Exception\GuzzleException;
use Sheba\MovieTicket\Vendor\BlockBuster\BlockBuster;
use Sheba\MovieTicket\Vendor\BlockBuster\VendorManager;

class MovieTicketManager
{
    /** @var VendorManager $vendorManager */
    private $vendorManager;
    /** @var MovieTicket $movieTicket */
    private $movieTicket;

    /**
     * MovieTicketManager constructor.
     * @param VendorManager $vendorManager
     * @param MovieTicket $movieTicket
     */
    public function __construct(VendorManager $vendorManager, MovieTicket $movieTicket)
    {
        $this->vendorManager = $vendorManager;
        $this->movieTicket = $movieTicket;
    }

    public function initVendor()
    {
        $this->vendorManager->setVendor(new BlockBuster())->initVendor();
        return $this;
    }

    public function getAvailableTickets()
    {
//        $availableMovies = $this->vendorManager->post(Actions::GET_MOVIE_LIST);
        return [];
    }

    /**
     * @return mixed
     * @throws GuzzleException
     */
    public function getVendorBalance(){

        return  $this->vendorManager->post(Actions::GET_VENDOR_BALANCE);
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
            return $availableTheatres;
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
            $status = $this->vendorManager->post(Actions::GET_THEATRE_SEAT_STATUS, ['DTMID' => $dtmid, 'Slot' => $slot]);
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
    public function bookSeats($data = array())
    {
        try {
            $bookingResponse = $this->vendorManager->post(Actions::REQUEST_MOVIE_TICKET_SEAT, $data);
            return $bookingResponse;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }
    /**
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function updateMovieTicketStatus($data = array())
    {
        try {
            $bookingResponse = $this->vendorManager->post(Actions::UPDATE_MOVIE_SEAT_STATUS, $data);
            return $bookingResponse;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    private function convertToJson($response)
    {
        return json_decode(json_encode($response));
    }
}
