<?php namespace Sheba\MovieTicket;

use App\Models\MovieTicketOrder;
use App\Models\MovieTicketVendor;
use Exception;
use Illuminate\Support\Facades\DB;
use Sheba\ModificationFields;
use Sheba\MovieTicket\Response\MovieResponse;
use Sheba\MovieTicket\Response\MovieTicketErrorResponse;
use Sheba\MovieTicket\Response\MovieTicketFailResponse;
use Sheba\MovieTicket\Response\MovieTicketSuccessResponse;
use Sheba\MovieTicket\Vendor\Vendor;
use Sheba\MovieTicket\Vendor\VendorFactory;

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
    /** @var MovieTicketRequest $movieTicketRequest */
    private $movieTicketRequest;
    /** @var MovieTicketValidator */
    private $validator;
    /** @var MovieTicketOrder $movieTicketOrder */
    private $movieTicketOrder;

    /**
     * MovieTicket constructor.
     * @param MovieTicketValidator $validator
     */
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
     * @param MovieTicketRequest $movieTicketRequest
     * @return $this
     */
    public function setMovieTicketRequest(MovieTicketRequest $movieTicketRequest)
    {
        $this->movieTicketRequest = $movieTicketRequest;
        return $this;
    }

    /**
     * @return MovieTicketOrder
     */
    public function getMovieTicketOrder()
    {
        return $this->movieTicketOrder;
    }

    /**
     * @param MovieTicketOrder $movieTicketOrder
     * @return $this
     */
    public function setMovieTicketOrder(MovieTicketOrder $movieTicketOrder)
    {
        $this->movieTicketOrder = $movieTicketOrder;
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

    public function validate()
    {
        if ($this->validator->setRequest($this->movieTicketRequest)->validate()->hasError()) return false;
        return true;
    }

    public function placeOrder()
    {
        $movie_ticket_order = $this->placeMovieTicketOrder($this->movieTicketRequest->getName(), $this->movieTicketRequest->getEmail(), $this->movieTicketRequest->getMobile(), $this->movieTicketRequest->getAmount());
        $this->movieTicketOrder = $movie_ticket_order;
        return $this;
    }

    public function disburseCommissions()
    {
        if ($this->movieTicketOrder->vendor) {
            if (get_class($this->movieTicketOrder->vendor) !== MovieTicketVendor::class)
                $this->movieTicketOrder->vendor = $this->movieTicketOrder->vendor = $this->movieTicketOrder->vendor->getModel();
        }
        $this->agent->getMovieTicketCommission()->setMovieTicketOrder($this->movieTicketOrder)->disburse();
        return $this;
    }
    public function disburseCommissionsNew(){
        if ($this->movieTicketOrder->vendor) {
            if (get_class($this->movieTicketOrder->vendor) !== MovieTicketVendor::class)
                $this->movieTicketOrder->vendor = $this->movieTicketOrder->vendor = $this->movieTicketOrder->vendor->getModel();
        }
        $this->agent->getMovieTicketCommission()->setMovieTicketOrder($this->movieTicketOrder)->disburseNew();
        return $this;
    }
    public function buyTicket()
    {
        $response = $this->vendor->buyTicket($this->movieTicketRequest);
        $this->vendor->deductAmount($this->movieTicketRequest->getAmount());
        return $response;
    }

    /**
     * @param MovieTicketRequest $movie_ticket_request
     * @throws Exception
     *
     * public function buyTicket(MovieTicketRequest $movie_ticket_request)
     * {
     * $this->response = $this->vendor->buyTicket($movie_ticket_request);
     * if ($this->response->hasSuccess()) {
     * $response = $this->response->getSuccess();
     * DB::transaction(function () use ($response, $movie_ticket_request) {
     * $movie_ticket_order = $this->placeMovieTicketOrder($movie_ticket_request->getName(), $movie_ticket_request->getEmail(),
     * $movie_ticket_request->getMobile(), $movie_ticket_request->getAmount());
     * $this->agent->getMovieTicketCommission()->setMovieTicketOrder($movie_ticket_order)->disburse();
     * $this->vendor->deductAmount($movie_ticket_request->getAmount());
     * $this->isSuccessful = true;
     * $this->movieTicketOrder = $movie_ticket_order;
     * $this->processSuccessfulMovieTicket($movie_ticket_order,$response);
     * });
     * }
     * return $this->response;
     * }*/

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
     * @param $name
     * @param $email
     * @param $mobile_number
     * @param $amount
     * @return MovieTicketOrder
     */
    private function placeMovieTicketOrder($name, $email, $mobile_number, $amount)
    {
        $movie_ticket_order = new MovieTicketOrder();
        $movie_ticket_order->agent_type = "App\\Models\\" . class_basename($this->agent);
        $movie_ticket_order->agent_id = $this->agent->id;
        $movie_ticket_order->reserver_name = $name;
        $movie_ticket_order->reserver_email = $email;
        $movie_ticket_order->reserver_mobile = $mobile_number;
        $movie_ticket_order->amount = $amount;
        $movie_ticket_order->status = 'initiated';
        $movie_ticket_order->transaction_id = null;
        $movie_ticket_order->vendor_id = $this->model->id;
        $movie_ticket_order->sheba_commission = ($amount * $this->model->sheba_commission) / (100 + $this->model->sheba_commission);
        $movie_ticket_order->reservation_details = json_encode(['trx_id' => $this->movieTicketRequest->getTrxId(), 'dtmsid' => $this->movieTicketRequest->getDtmsId(), 'lid' => $this->movieTicketRequest->getTicketId(), 'image_url' => $this->movieTicketRequest->getImageUrl(), 'original_price' => $amount - (($amount * $this->model->sheba_commission) / (100 + $this->model->sheba_commission))]);
        if (!empty($this->movieTicketRequest->getVoucher())) {
            $movie_ticket_order->voucher_id = $this->movieTicketRequest->getVoucher()->id;
        }
        $movie_ticket_order->discount = $this->movieTicketRequest->getDiscount() ?: 0.00;
        $movie_ticket_order->discount_percent = $this->movieTicketRequest->getDiscountPercent() ?: 0.00;
        $movie_ticket_order->sheba_contribution = $this->movieTicketRequest->getShebaContribution() ?: 0.00;
        $movie_ticket_order->vendor_contribution = $this->movieTicketRequest->getVendorContribution() ?: 0.00;

        $this->setModifier($this->agent);
        $this->withCreateModificationField($movie_ticket_order);
        $movie_ticket_order->save();

        $movie_ticket_order->agent = $this->agent;
        $movie_ticket_order->vendor = $this->model;

        return $movie_ticket_order;
    }

    /**
     * @param MovieTicketOrder $movie_ticket_order
     * @param MovieTicketFailResponse $movieTicketFailResponse
     * @return bool
     */
    public function processFailedMovieTicket(MovieTicketOrder $movie_ticket_order, MovieTicketFailResponse $movieTicketFailResponse)
    {
        if ($movie_ticket_order->isFailed()) return true;
        DB::transaction(function () use ($movie_ticket_order, $movieTicketFailResponse) {
            $this->model = $movie_ticket_order->vendor;
            removeRelationsAndFields($movie_ticket_order, ['agent', 'vendor']);
            $movie_ticket_order->status = 'failed';
            $movie_ticket_order->reservation_details = json_encode($movieTicketFailResponse->getFailedTransactionDetails());
            $this->setModifier($this->agent);
            $this->withUpdateModificationField($movie_ticket_order);
            $movie_ticket_order->update();
            $vendor = new VendorFactory();
            $vendor = $vendor->getById($movie_ticket_order->vendor_id);

            $movie_ticket_order->agent = $this->agent;
            $movie_ticket_order->vendor = $vendor->getModel();
            $this->refund($movie_ticket_order);

            $vendor->refill($movie_ticket_order->amount);
        });

    }

    /**
     * @param MovieTicketOrder $movie_ticket_order
     * @param MovieTicketSuccessResponse $success_response
     * @return bool
     */
    public function processSuccessfulMovieTicket(MovieTicketOrder $movie_ticket_order, MovieTicketSuccessResponse $success_response)
    {
        if ($movie_ticket_order->isSuccess()) return true;
        removeRelationsAndFields($movie_ticket_order, ['agent', 'vendor']);
        DB::transaction(function () use ($movie_ticket_order, $success_response) {
            $movie_ticket_order->status = 'confirmed';
            $movie_ticket_order->transaction_id = $success_response->transactionId;
            $movie_ticket_order->reservation_details = json_encode($success_response->transactionDetails);
            $movie_ticket_order->update();
        });
        $this->isSuccessful = true;
        return true;
    }

    /**
     * @param $agent
     * @return string
     * @throws Exception
     */
    public function resolveAgentType($agent)
    {
        return lcfirst(class_basename($agent));
    }

    /**
     * @param MovieTicketOrder $movie_ticket_order
     */
    public function refund(MovieTicketOrder $movie_ticket_order)
    {
        $movie_ticket_order->agent->getMovieTicketCommission()->setMovieTicketOrder($movie_ticket_order)->refund();
    }
}
