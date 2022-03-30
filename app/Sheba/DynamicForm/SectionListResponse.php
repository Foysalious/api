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
     * @param mixed $can_apply
     * @return SectionListResponse
     */
    public function setCanApply($can_apply): SectionListResponse
    {
        $this->can_apply = $can_apply;
        return $this;
    }

    /**
     * @param mixed $overall_completion
     * @return SectionListResponse
     */
    public function setOverallCompletion($overall_completion): SectionListResponse
    {
        $this->overall_completion = $overall_completion;
        return $this;
    }

    /**
     * @param string $message
     * @return SectionListResponse
     */
    public function setMessage(string $message): SectionListResponse
    {
        $this->message = $message;
        return $this;
    }
}