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

    public function mergeWithDbGateways($userType){
        $dto = $this->dto->toArray();
        if ($gateway = $this->dbGateways->where('method_name', $dto['method_name'])->first()){
            $dto['name'] = $gateway->name_en;
            $dto['name_bn'] = $gateway->name_bn;
            $dto['cash_in_charge'] = $gateway->cash_in_charge;
            return $dto;
        }

        if ( $userType == 'partner' && $dto['method_name'] == 'online' && $maxCashInCharge = $this->dbGateways->whereIn('method_name', ['ssl', 'port_wallet'])){
            $dto['cash_in_charge'] = $maxCashInCharge->max('cash_in_charge');
            return $dto;
        }

        if ($userType == 'partner') {
            return null;
        }

        return $dto;
    }

    public function toArray()
    {
        return $this->dto->toArray();
    }
}
