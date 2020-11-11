<?php namespace Sheba\NeoBanking\Banks;

class CompletionCalculation
{
    private $count, $filled;

    private $skipFields;

    private $id = [], $filled_id = [];

    public function __construct($skipFields = [])
    {
        $this->count  = 0;
        $this->filled = 0;
        $this->skipFields = $skipFields;
    }

    public function get($allData)
    {
        foreach ($allData as $data)
        {
            if ($data['field_type'] == 'multipleView')
                foreach ($data["views"] as $multiView)
                    $this->calculate($multiView);

            $this->calculate($data);
        }
        return ($this->filled / $this->count) * 100;
    }

    private function calculate($data)
    {
        if ($data['field_type'] !== 'header' && $data['field_type'] !== 'multipleView' && $data['field_type'] !== 'textView')
        {
            if (!in_array($data['id'], $this->skipFields))
            {
                if($data['value'] !== ''){
                    $this->filled++;
                    $this->filled_id[] = $data['id'];
                }
                $this->count++;
                $this->id[] = $data['id'];
            }
        }
    }
}