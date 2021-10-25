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

    /**
     * @param $allData
     * @return float|int
     */
    public function get($allData)
    {
        foreach ($allData as $data) {
            if ($data['field_type'] == 'multipleView') {
                foreach ($data["views"] as $multiView) {
                    $this->calculate($multiView);
                }
            }

            elseif ($data['field_type'] == 'radioGroup' || $data['field_type'] == 'conditionalSelect') $this->calculateGroupedViews($data);
            else $this->calculate($data);
        }
        return $this->count ? ($this->filled / $this->count) * 100 : 100;
    }

    /**
     * @param $data
     */
    private function calculate($data)
    {
        if ($data['field_type'] !== 'header' && $data['field_type'] !== 'multipleView' && $data['field_type'] !== 'textView' && $data['mandatory'] !== false) {
            if (!in_array($data['id'], $this->skipFields)) {
                if($data['value'] !== '') {
                    $this->filled++;
                    $this->filled_id[] = $data['id'];
                }
                $this->count++;
                $this->id[] = $data['id'];
            }
        }
    }

    private function calculateGroupedViews($data){
        if (!in_array($data['id'], $this->skipFields) && $data['mandatory'] !== false)
        {
            foreach ($data['views'] as $view){
                if(($data['field_type'] == 'radioGroup' && $view['value'] == 1) || ($data['field_type'] == 'conditionalSelect' && $view['value'] !== '')){
                    $this->filled++;
                    $this->filled_id[] = $data['id'];
                    break;
                }
            }
            $this->count++;
            $this->id[] = $data['id'];
        }
    }
}