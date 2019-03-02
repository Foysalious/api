<?php namespace Sheba\MovieTicket;

use App\Models\MovieTicketOrder;
use App\Models\MovieTicketVendor;
use Illuminate\Support\Facades\DB;
use Sheba\ModificationFields;
use Sheba\MovieTicket\Response\BlockBusterResponse;
use Sheba\MovieTicket\Response\MovieResponse;
use Sheba\MovieTicket\Response\MovieTicketErrorResponse;
use Sheba\MovieTicket\Response\MovieTicketSuccessResponse;
use Sheba\MovieTicket\Vendor\Vendor;

class MovieTicket
{
    use ModificationFields;
    /** @var Vendor */
    private $vendor;
    /** @var MovieTicketVendor */
    private $model;
    /** @var MovieAgent */
    private $agent;

    private $isSuccessful;

    /** @var MovieResponse */
    private $response;

    /** @var MovieTicketValidator */
    private $validator;

    public function __construct(MovieTicketValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param MovieAgent $agent
     * @return $this
     */
    public function setAgent(MovieAgent $agent)
    {
        $this->agent = $agent;
        $this->validator->setAgent($agent);
        return $this;
    }

    /**
     * @param Vendor $model
     * @return $this
     */
    public function setVendor(Vendor $model)
    {
        $this->vendor = $model;
        $this->model = $this->vendor->getModel();
        $this->validator->setVendor($model);
        return $this;
    }

    /**
     * @param MovieTicketRequest $movie_ticket_request
     */
    public function buyTicket(MovieTicketRequest $movie_ticket_request)
    {
        if ($this->validator->setRequest($movie_ticket_request)->validate()->hasError()) return;
        $this->response = $this->vendor->buyTicket($movie_ticket_request->getBlockBusterResponse());
        if ($this->response->hasSuccess()) {
            $response = $this->response->getSuccess();
            DB::transaction(function () use ($response, $movie_ticket_request) {
                $movie_ticket_order = $this->placeMovieTicketOrder($response, $movie_ticket_request->getMobile(), $movie_ticket_request->getAmount());
                $this->agent->getMovieTicketCommission()->setMovieTicketOrder($movie_ticket_order)->disburse();
                $this->vendor->deductAmount($movie_ticket_request->getAmount());
                $this->isSuccessful = true;
            });
        }
    }

    /**
     * @return bool
     */
    public function isNotSuccessful()
    {
        return !$this->isSuccessful;
    }

    /**
     * @return MovieTicketErrorResponse
     */
    public function getError()
    {
        if ($this->validator->hasError()) {
            return $this->validator->getError();
        } else if (!$this->response->hasSuccess()) {
            return $this->response->getError();
        } else {
            if (!$this->isSuccessful) return new MovieTicketErrorResponse();
        }
        return new MovieTicketErrorResponse();
    }

    /**
     * @param MovieTicketSuccessResponse $response
     * @param $mobile_number
     * @param $amount
     * @return MovieTicketOrder
     */
    private function placeMovieTicketOrder(MovieTicketSuccessResponse $response, $mobile_number, $amount)
    {
        $movie_ticket_order = new MovieTicketOrder();
        $movie_ticket_order->agent_type = "App\\Models\\" . class_basename($this->agent);
        $movie_ticket_order->agent_id = $this->agent->id;
        $movie_ticket_order->reserver_mobile = $mobile_number;
        $movie_ticket_order->amount = $amount;
        $movie_ticket_order->status = 'confirmed';
        $movie_ticket_order->transaction_id = $response->transactionId;
        $movie_ticket_order->reservation_details = json_encode($response->transactionDetails);
        $movie_ticket_order->vendor_id = $this->model->id;
        $movie_ticket_order->sheba_commission = ($amount * $this->model->sheba_commission) / 100;

        $this->setModifier($this->agent);
        $this->withCreateModificationField($movie_ticket_order);
        $movie_ticket_order->save();

        $movie_ticket_order->agent = $this->agent;
        $movie_ticket_order->vendor = $this->model;

        return $movie_ticket_order;
    }

//    /**
//     * @param MovieTicketOrder $movie_ticket_order
//     * @param MovieTicketErrorResponse $top_up_fail_response
//     * @return bool
//     */
//    public function processFailedTopUp(MovieTicketOrder $movie_ticket_order, MovieTicketErrorResponse $movieTicketErrorResponse)
//    {
//        if ($movie_ticket_order->isFailed()) return true;
//        DB::transaction(function () use ($movie_ticket_order, $movieTicketErrorResponse) {
//            $this->model = $movie_ticket_order->vendor;
//            $movie_ticket_order->status = config('topup.status.failed')['sheba'];
//            $movie_ticket_order->transaction_details = json_encode($movieTicketErrorResponse->getFailedTransactionDetails());
//            $this->setModifier($this->agent);
//            $this->withUpdateModificationField($movie_ticket_order);
//            $movie_ticket_order->update();
//            $this->refund($movie_ticket_order);
//            $vendor = new VendorFactory();
//            $vendor = $vendor->getById($movie_ticket_order->vendor_id);
//            $vendor->refill($movie_ticket_order->amount);
//        });
//    }

    /**
     * @param MovieTicketOrder $movie_ticket_order
     * @param BlockBusterResponse $success_response
     * @return bool
     */
    public function processSuccessfulMovieTicket(MovieTicketOrder $movieTicketOrder, BlockBusterResponse $success_response)
    {
        if ($movieTicketOrder->isSuccess()) return true;
        DB::transaction(function () use ($movieTicketOrder, $success_response) {
            $movieTicketOrder->status = 'confirmed';
            $movieTicketOrder->transaction_details = json_encode($success_response->getSuccess());
            $movieTicketOrder->update();
        });
    }

//    /**
//     * @param TopUpOrder $movie_ticket_order
//     */
//    public function refund(TopUpOrder $movie_ticket_order)
//    {
//        $movie_ticket_order->agent->getCommission()->setTopUpOrder($movie_ticket_order)->refund();
//    }
}