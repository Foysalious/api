<?php

namespace Sheba\MerchantEnrollment;

use App\Models\Partner;

class CompletionCalculation
{
    private $count = 0, $filled = 0;

    private $skipFields = [];

    private $formFields;

    private $filled_data = [];

    /**
     * @param mixed $skipFields
     * @return CompletionCalculation
     */
    public function setSkipFields($skipFields): CompletionCalculation
    {
        $this->skipFields = $skipFields;
        return $this;
    }

    /**
     * @param mixed $formFields
     * @return CompletionCalculation
     */
    public function setFormFields($formFields): CompletionCalculation
    {
        $this->formFields = $formFields;
        return $this;
    }

    public function get()
    {
        foreach ($this->formFields as $field)
            $this->calculate($field);

        return $this->count ? ($this->filled / $this->count) * 100 : 100;
    }

    public function calculate($data)
    {
        if ($data['input_type'] !== 'header') {
            if (!in_array($data['id'], $this->skipFields)) {
                if ($data['data'] !== '') {
                    $this->filled++;
                    $this->filled_data[$data['id']] = $data['data'];
                }
                $this->count++;
                $this->filled_data[$data['id']] = '';
            }
        }
    }
}