<?php namespace Sheba\Business\Procurement\Code;

use App\Models\Procurement;

class Builder extends Machine
{
    /**
     * @param Procurement $procurement
     * @return string
     */
    public function workOrder(Procurement $procurement)
    {
        return 'W' . $this->baseCodeByProcurement($procurement);
    }

    /**
     * @param Procurement $procurement
     * @return string
     */
    public function invoice(Procurement $procurement)
    {
        return 'I' . $this->baseCodeByProcurement($procurement);
    }

    /**
     * @param Procurement $procurement
     * @return string
     */
    public function bill(Procurement $procurement)
    {
        return 'B' . $this->baseCodeByProcurement($procurement);
    }

    /**
     * @param Procurement $procurement
     * @return string
     */
    public function order(Procurement $procurement)
    {
        return 'P' . $this->baseCodeByProcurement($procurement);
    }

    /**
     * @param Procurement $procurement
     * @return string
     */
    private function baseCodeByProcurement(Procurement $procurement)
    {
        return self::SEPARATOR . $this->getProcurementFormat($procurement) . self::SEPARATOR . $this->getBidCodeByProcurement($procurement);
    }

    /**
     * @param Procurement $procurement
     * @return string
     */
    private function getProcurementFormat(Procurement $procurement)
    {
        return str_pad($procurement->id, self::PROCUREMENT_CODE_LENGTH, self::PAD_STRING, STR_PAD_LEFT);
    }

    private function getBidCodeByProcurement(Procurement $procurement)
    {
        $bid = $procurement->getActiveBid();
        if (!$bid) return '000000';
        return str_pad($bid->id, self::BID_CODE_LENGTH, self::PAD_STRING, STR_PAD_LEFT);
    }
}
