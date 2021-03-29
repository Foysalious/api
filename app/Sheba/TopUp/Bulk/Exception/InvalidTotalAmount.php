<?php namespace Sheba\TopUp\Bulk\Exception;

use App\Exceptions\DoNotReportException;
use Throwable;

class InvalidTotalAmount extends DoNotReportException
{
    /** @var float $totalRechargeAmount */
    private $totalRechargeAmount;
    /** @var float $totalBalance */
    private $totalBalance;

    /**
     * InvalidTotalAmount constructor.
     * @param $total_recharge_amount
     * @param $total_balance
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($total_recharge_amount, $total_balance, $message = 'You do not have sufficient balance to recharge.', $code = 400, Throwable $previous = null)
    {
        $this->totalRechargeAmount = $total_recharge_amount;
        $this->totalBalance = $total_balance;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return float
     */
    public function getTotalRechargeAmount(): float
    {
        return $this->totalRechargeAmount;
    }

    /**
     * @return float
     */
    public function getTotalBalance(): float
    {
        return $this->totalBalance;
    }
}
