<?php namespace Sheba\TopUp;

use App\Jobs\SendTopUpPushNotification;
use App\Models\Affiliate;
use App\Models\Partner;
use App\Models\TopUpOrder;
use Exception;
use App\Models\TopUpVendor;
use Sheba\Dal\TopupOrder\TopUpOrderRepository;
use Sheba\ModificationFields;
use Sheba\PushNotificationHandler;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\TopUp\Vendor\Response\TopUpErrorResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TopUp\Vendor\Response\TopUpSuccessResponse;
use Sheba\TopUp\Vendor\Response\TopUpSystemErrorResponse;
use Sheba\TopUp\Vendor\Vendor;

class TopUpRechargeManager extends TopUpManager
{
    use ModificationFields;

    /** @var TopUpValidator */
    private $validator;
    /** @var TopUpOrderRepository */
    private $orderRepo;

    /** @var Vendor */
    private $vendor;
    /** @var TopUpVendor */
    private $vendorModel;
    /** @var TopUpAgent */
    private $agent;
    /** @var boolean */
    private $isSuccessful;
    /** @var TopUpResponse */
    private $response;

    public function __construct(TopUpValidator $validator, StatusChanger $status_changer, TopUpOrderRepository $order_repo)
    {
        parent::__construct($status_changer);
        $this->validator = $validator;
        $this->orderRepo = $order_repo;
    }

    /**
     * @param TopUpAgent $agent
     * @return $this
     */
    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        $this->validator->setAgent($agent);
        $this->setModifier($this->agent);
        return $this;
    }

    /**
     * @param Vendor $vendor
     * @return $this
     */
    public function setVendor(Vendor $vendor)
    {
        $this->vendor = $vendor;
        $this->vendorModel = $this->vendor->getModel();
        $this->validator->setVendor($vendor);
        return $this;
    }

    /**
     * @param TopUpOrder $order
     * @return $this
     * @throws Exception
     */
    public function setTopUpOrder(TopUpOrder $order)
    {
        parent::setTopUpOrder($order);
        $this->setAgent($order->agent)->setVendor($this->getVendor());
        $this->validator->setTopupOrder($this->topUpOrder);
        return $this;
    }

    /**
     * @throws \Throwable
     */
    public function recharge()
    {
        $is_error = 0;
        if ($this->validator->validate()->hasError()) {
            $this->updateFailedTopOrder($this->validator->getError());
            $is_error = 1;
            return;
        }

        $this->statusChanger->attempted();

        $this->response = $this->vendor->recharge($this->topUpOrder);

        if ($this->response->hasError()) {
            $is_error = 1;
            $this->updateFailedTopOrder($this->response->getErrorResponse());
            return;
        }
        if ($this->agent instanceof Partner || $this->agent instanceof Affiliate) $this->sendPushNotification($is_error);

        $this->handleSuccessfulTopUpByVendor();
    }

    /**
     * @return bool
     */
    public function isNotSuccessful()
    {
        return !$this->isSuccessful;
    }

    /**
     * @return TopUpErrorResponse
     * @throws Exception
     */
    public function getError()
    {
        if ($this->validator->hasError()) return $this->validator->getError();
        if ($this->response->hasError()) return $this->response->getErrorResponse();
        if (!$this->isSuccessful) return new TopUpSystemErrorResponse();
        return new TopUpErrorResponse();
    }

    /**
     * @throws \Throwable
     */
    private function handleSuccessfulTopUpByVendor()
    {
        $this->doTransaction(function () {
            $this->topUpOrder = $this->updateSuccessfulTopOrder($this->response->getSuccess());
            $this->agent->getCommission()->setTopUpOrder($this->topUpOrder)->disburse();
            $this->vendor->deductAmount($this->topUpOrder->amount);
            $this->orderRepo->update($this->topUpOrder, [ 'is_agent_debited' => 1 ]);
        });

        if ($this->topUpOrder->isSuccess()) {
            app()->make(ActionRewardDispatcher::class)->run('top_up', $this->agent, $this->topUpOrder);
        }
        $this->isSuccessful = true;
    }

    /**
     * @param TopUpSuccessResponse $response
     * @return TopUpOrder
     */
    private function updateSuccessfulTopOrder(TopUpSuccessResponse $response)
    {
        $id = $response->getTransactionId();
        $details = $response->getTransactionDetails();

        $topup_order = $response->isPending() ?
            $this->statusChanger->pending($details, $id) :
            $this->statusChanger->successful($details, $id);

        return $this->setAgentAndVendor($topup_order);
    }

    /**
     * @param TopUpErrorResponse $response
     * @return TopUpOrder
     */
    private function updateFailedTopOrder(TopUpErrorResponse $response)
    {
        $topup_order = $this->statusChanger->failed(FailDetails::buildFromErrorResponse($response));
        return $this->setAgentAndVendor($topup_order);
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpOrder
     */
    private function setAgentAndVendor(TopUpOrder $topup_order)
    {
        $topup_order->agent = $this->agent;
        $topup_order->vendor = $this->vendorModel;
        return $topup_order;
    }

    private function sendPushNotification($is_error)
    {
        $title = "অভিনন্দন";
        $message = "অভিনন্দন, আপনার টপ-আপ রিচার্জটি সফলভাবে সম্পন্ন হয়েছে।";
        if ($is_error) {
            $title = "দুঃখিত";
            $message = "দুঃখিত, কারিগরি ত্রুটির কারনে আপনার টপ-আপ রিচার্জ সফল হয়নি। অনুগ্রহ করে আবার চেষ্টা করুন।";
        }
        if ($this->agent instanceof Partner){
            $topic = config('sheba.push_notification_topic_name.manager') . $this->agent->id;
            $channel = config('sheba.push_notification_channel_name.manager');
        }
        if ($this->agent instanceof Affiliate){
            config('sheba.push_notification_channel_name.customer') . $this->agent->id;
            config('sheba.push_notification_channel_name.customer');
        }

        $notification_data = [
            "title" => $title,
            "message" => $message,
            "event_type" => 'TopUp',
            "sound" => "notification_sound",
            "channel_id" => $channel
        ];
        (new PushNotificationHandler())->send($notification_data, $topic, $channel);
    }
}
