<?php

namespace App\Sheba\DynamicForm;

use Sheba\Dal\MefFields\Model as MefFields;

class CompletionCalculation
{
    const HEADER = "header";

    /*** @var MefFields */
    private $fields;

    /**
     * @param mixed $fields
     * @return CompletionCalculation
     */
    public function setFields($fields): CompletionCalculation
    {
        $this->fields = $fields;
        return $this;
    }

    public function calculate()
    {
        $total = 0;
        $filled = 0;
        foreach ($this->fields as $field) {
            if($field['input_type'] !== self::HEADER) {
                $total++;

                if (!empty($field["data"])) $filled++;
            }
        }

        return $total === 0 ? 100 : round(($filled / $total) * 100, 2);
    }
}