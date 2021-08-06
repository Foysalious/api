<?php namespace App\Sheba\Voucher;

use App\Models\PosCustomer;
use Exception;
use Sheba\Voucher\DTO\Params\CheckParamsForPosOrder;

class VoucherValidate extends VoucherService
{

    private $posCustomer;

    public function setPosCustomer($posCustomer)
    {
        $this->posCustomer = $posCustomer;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function validate()
    {
        $pos_order_params = (new CheckParamsForPosOrder());
        $pos_order_params->setOrderAmount($this->amount);
        $pos_order_params = $pos_order_params->setApplicant($this->posCustomer);
        $pos_order_params = $pos_order_params->setPartnerPosService($this->posServices);
        $result = voucher($this->code)->checkForPosOrder($pos_order_params);
        $result = $this->posCustomer instanceof PosCustomer ? $result->reveal() : $result->checkMobile($this->posCustomer->getCustomerInfo()['mobile'])->reveal();
        $voucher = [];
        if ($result['is_valid']) {
            $voucher = $result['voucher'];
            $voucher = [
                'amount' => (double)$result['amount'],
                'code' => $voucher->code,
                'id' => $voucher->id,
                'title' => $voucher->title
            ];
        }

        return $voucher;
    }
}
