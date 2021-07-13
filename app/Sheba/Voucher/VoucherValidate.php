<?php namespace App\Sheba\Voucher;


use App\Models\PosCustomer;
use Illuminate\Database\Eloquent\Model;
use Sheba\Voucher\DTO\Params\CheckParamsForPosOrder;

class VoucherValidate
{
    private $real_pos_customer;
    private $customer;

    public function setPartner($partner_id)
    {
        $this->partnerId = $partner_id;
        return $this;
    }

    public function setRealPosCustomer($real_pos_customer)
    {
        $this->real_pos_customer = $real_pos_customer;
        return $this;
    }

    public function setPosCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function posOrderParams($request)
    {
        $pos_order_params = (new CheckParamsForPosOrder());
        $pos_order_params->setOrderAmount($request->amount);
        $pos_order_params = $this->real_pos_customer ? $pos_order_params->setApplicant($this->customer) : $pos_order_params->setApplicant(new PosCustomer());
        return $pos_order_params->setPartnerPosService($request->pos_services);
    }

    public function OrderVoucherResult($request, $pos_order_params)
    {
        $result = voucher($request->code)->checkForPosOrder($pos_order_params);
        return $this->real_pos_customer ? $result->reveal() : $result->checkMobile($this->customer['mobile'])->reveal();
    }

    public function voucherValidate($request)
    {
        $pos_order_params = $this->posOrderParams($request);
        $result = $this->OrderVoucherResult($request, $pos_order_params);

        if ($result['is_valid']) {
            $voucher = $result['voucher'];
            $voucher = [
                'amount' => (double)$result['amount'],
                'code' => $voucher->code,
                'id' => $voucher->id,
                'title' => $voucher->title
            ];
            return api_response($request, null, 200, ['voucher' => $voucher]);
        } else {
            return api_response($request, null, 403, ['message' => 'Invalid Promo']);
        }
    }

}
