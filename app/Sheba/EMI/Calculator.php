<?php namespace Sheba\EMI;

use Sheba\Dal\DigitalCollectionSetting\Model as DigitalCollectionSettingModel;
use Sheba\PaymentLink\PaymentLinkStatics;

class Calculator
{
    protected $partner;

    /**
     * @param $partner
     * @return $this
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }
    /**
     * @param $amount
     * @return array
     */
    public function getCharges($amount)
    {
        $emi = collect([]);
        foreach ($this->getInterestRatesBreakDowns() as $item) {
            $emi->push($this->calculateMonthWiseCharge($amount, $item['month'], $item['interest']));
        }
        return $emi->forgetEach('interest_value')->toArray();
    }

    /**
     * @param $amount
     * @param $month
     * @param $interest
     * @param bool $format
     * @return array
     */
    public function calculateMonthWiseCharge($amount, $month, $interest, $format = true)
    {
        $rate                 = ($interest / 100);
        $interest_two_decimal = number_format((float)$interest, 2, '.', '');
        $bank_trx_fee = isset($this->partner) ? $this->getBankTransactionFee($amount + ceil(($amount * $rate))) :
                            $this->getBankTransactionFee($amount + ceil(($amount * $rate))) + $this->getTax();
        return $format ? [
            "number_of_months"     => $month,
            "interest"             => "$interest_two_decimal%",
            "interest_value"       => $interest,
            "total_interest"       => number_format(ceil(($amount * $rate))),
            "bank_transaction_fee" => number_format($bank_trx_fee),
            "amount"               => number_format(ceil((($amount + ($amount * $rate)) + $bank_trx_fee) / $month)),
            "total_amount"         => number_format(($amount + ceil(($amount * $rate))) + $bank_trx_fee)
        ] : [
            "number_of_months"     => $month,
            "interest"             => $interest,
            "interest_value"       => $interest,
            "total_interest"       => ceil(($amount * $rate)),
            "bank_transaction_fee" => $bank_trx_fee,
            "amount"               => ceil((($amount + ($amount * $rate)) + $bank_trx_fee) / $month),
            "total_amount"         => ($amount + ceil(($amount * $rate))) + $bank_trx_fee
        ];
    }

    /**
     * @param $amount
     * @return bool|\Carbon\Carbon|float|\Illuminate\Support\Collection|int|mixed|string|null
     */
    public function getBankTransactionFee($amount)
    {
        $digitalCollectionPartner = null;

        if (isset($this->partner->id)) {
            $digitalCollectionPartner = DigitalCollectionSettingModel::query()->where('partner_id', $this->partner->id)->first();
        }
        if ($digitalCollectionPartner) {
            $charge = $digitalCollectionPartner->service_charge;
            return ceil($amount * ($charge/ 100));
        }
        return ceil($amount * ($this->getBankFeePercentage() / 100));
    }

    public function getMonthData($amount, $month, $format = true)
    {
        $data = $this->getMonthInterest($month);

        return empty($data) ? [] : $this->calculateMonthWiseCharge($amount, $data['month'], $data['interest'], $format);
    }

    public function getTax()
    {
        return PaymentLinkStatics::get_payment_link_tax();
    }

    public function getMonthInterest($month)
    {
        $breakdowns = $this->getInterestRatesBreakDowns();
        $data       = array_values(array_filter($breakdowns, function ($item) use ($month) {
            return $item['month'] == $month;
        }));
        return !empty($data) ? $data[0] : [];
    }

    public function getInterestRatesBreakDowns()
    {
        return config('emi.breakdowns');
    }

    public function getBankFeePercentage()
    {
        return config('emi.bank_fee_percentage');
    }
}
