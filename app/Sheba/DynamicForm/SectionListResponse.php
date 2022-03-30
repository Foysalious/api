<?php

namespace App\Sheba\DynamicForm;

use Illuminate\Contracts\Support\Arrayable;

class SectionListResponse implements Arrayable
{
    private $categories;
    private $can_apply;
    private $overall_completion;
    private $message = '';

    public function toArray(): array
    {
        return [
            "categories" => $this->categories,
            "can_apply" => $this->can_apply,
            "overall_completion" => $this->overall_completion,
            "message" => $this->message,
        ];
    }

    /**
     * @param mixed $categories
     * @return SectionListResponse
     */
    public function setCategories($categories): SectionListResponse
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @param mixed $completion
     * @return SectionListResponse
     */
    public function setCanApply($completion): SectionListResponse
    {
        $this->can_apply = $completion === 100 ? 1 : 0;
        return $this;
    }

    /**
     * @param mixed $overall_completion
     * @return SectionListResponse
     */
    public function setOverallCompletion($overall_completion): SectionListResponse
    {
        $this->overall_completion = [
            "en" => $overall_completion,
            "bn" => convertNumbersToBangla($overall_completion, false)
        ];
        return $this;
    }

    /**
     * @param $message
     * @return SectionListResponse
     */
    public function setMessage($message): SectionListResponse
    {
        $this->message = $message;
        return $this;
    }
}