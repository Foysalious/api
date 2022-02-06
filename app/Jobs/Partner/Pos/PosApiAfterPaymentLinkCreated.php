<?php

namespace App\Jobs\Partner\Pos;

use App\Jobs\Job;
use App\Sheba\Pos\Repositories\PosClientRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PosApiAfterPaymentLinkCreated extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $partner_id;
    private $pos_order_id;
    private $payment_link;

    public function __construct()
    {

    }

    public function handle()
    {
        try {
            if ($this->attempts() <= 1) {
                /** @var PosClientRepository $posClientRepository */
                $posClientRepository = app(PosClientRepository::class);
                $data = $posClientRepository->paymentLinkCreateData($this->payment_link);
                $url = $posClientRepository->setOrderId($this->pos_order_id)->setPartnerId($this->partner_id)
                    ->makePaymentLinkCreateApi();
                $posClientRepository->post($url, $data);
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    /**
     * @param mixed $partner_id
     * @return PosApiAfterPaymentLinkCreated
     */
    public function setPartnerId($partner_id): PosApiAfterPaymentLinkCreated
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * @param mixed $pos_order_id
     * @return PosApiAfterPaymentLinkCreated
     */
    public function setPosOrderId($pos_order_id): PosApiAfterPaymentLinkCreated
    {
        $this->pos_order_id = $pos_order_id;
        return $this;
    }

    /**
     * @param mixed $payment_link
     * @return PosApiAfterPaymentLinkCreated
     */
    public function setPaymentLink($payment_link): PosApiAfterPaymentLinkCreated
    {
        $this->payment_link = $payment_link;
        return $this;
    }

}
