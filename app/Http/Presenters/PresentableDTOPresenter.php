<?php namespace App\Http\Presenters;

use Sheba\PresentableDTO;

class PresentableDTOPresenter extends Presenter
{
    /** @var PresentableDTO */
    private $dto;
    private $dbGateways;

    public function __construct(PresentableDTO $dto, $dbGateways)
    {
        $this->dto = $dto;
        $this->dbGateways = $dbGateways;
    }

    public function mergeWithDbGateways($userType, $payableType){
        $dto = $this->dto->toArray();

        if ($userType === 'customer') {
            if ($payableType === 'order') {
                if ($gateway = $this->dbGateways->where('method_name', $dto['method_name'])->first()) $dto['discount_message'] = $gateway->discount_message;
                else $dto['discount_message'] = '';
            }
            return $dto;
        }

        if ($gateway = $this->dbGateways->where('method_name', $dto['method_name'])->first()){
            $dto['name'] = $gateway->name_en;
            $dto['name_bn'] = $gateway->name_bn;
            $dto['cash_in_charge'] = $gateway->cash_in_charge;
            $dto['order'] = $gateway->order;
            return $dto;
        }
        $onlineMethods = $this->dbGateways->whereIn('method_name', ['ssl', 'port_wallet']);
        if ( $userType == 'partner' && $dto['method_name'] == 'online' && count($onlineMethods) > 0){
            $maxCashInCharge = $onlineMethods->max('cash_in_charge');
            $method = $onlineMethods->where('cash_in_charge', $maxCashInCharge)->first();
            $dto['cash_in_charge'] = $maxCashInCharge;
            $dto['asset'] = $method->asset_name;
            $dto['order'] = $method['order'];
            $dto['name_bn'] =$dto['name'] =='City Bank (American Express)'?'City Bank ': 'ভিসা/মাস্টার ও অন্যান্য';
            return $dto;
        }

        if ($userType == 'partner' && $payableType == 'wallet_recharge') {
            return null;
        }

        return $dto;
    }

    public function toArray()
    {
        return $this->dto->toArray();
    }
}
