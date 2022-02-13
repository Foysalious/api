<?php

namespace App\Sheba\MerchantEnrollment\PaymentMethod;

use Illuminate\Contracts\Support\Arrayable;
use Sheba\NeoBanking\Traits\ProtectedGetterTrait;

class CompletionDetail implements Arrayable
{
    use ProtectedGetterTrait;

    protected $completion_percentage;
    protected $title;
    private   $last_updated;
    protected $category_code;

    /**
     * @param mixed $completion_percentage
     */
    public function setCompletionPercentage($completion_percentage): CompletionDetail
    {
        $this->completion_percentage = $completion_percentage;
        return $this;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): CompletionDetail
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param mixed $last_updated
     */
    public function setLastUpdated($last_updated): CompletionDetail
    {
        $this->last_updated = $last_updated;
        return $this;
    }

    /**
     * @param mixed $code
     */
    public function setCategoryCode($code): CompletionDetail
    {
        $this->category_code = $code;
        return $this;
    }

}
