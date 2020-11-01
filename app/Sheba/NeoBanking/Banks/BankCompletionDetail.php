<?php


namespace Sheba\NeoBanking\Banks;


use Illuminate\Contracts\Support\Arrayable;
use Sheba\NeoBanking\Traits\ProtectedGetterTrait;

class BankCompletionDetail implements Arrayable
{
    use ProtectedGetterTrait;

    protected $completion_percentage;
    protected $title;
    protected $last_updated;
    protected $code;

    /**
     * @param mixed $completion_percentage
     * @return BankCompletionDetail
     */
    public function setCompletionPercentage($completion_percentage)
    {
        $this->completion_percentage = $completion_percentage;
        return $this;
    }

    /**
     * @param mixed $title
     * @return BankCompletionDetail
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param mixed $last_updated
     * @return BankCompletionDetail
     */
    public function setLastUpdated($last_updated)
    {
        $this->last_updated = $last_updated;
        return $this;
    }

    /**
     * @param mixed $code
     * @return BankCompletionDetail
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

}
