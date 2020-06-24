<?php namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class DistrictsWithThanasTransformer extends TransformerAbstract {
    public function transform($districts)
    {
        return [
            'data'     => $this->getFormattedData($districts)
        ];
    }
    private function getFormattedData($districts) {
        $data = [];
        foreach ($districts as $district){
            $data[] = [
                "id" => $district->id,
                "name" => $district->name,
                "bn_name" => $district->bn_name,
                "thanas" => $this->getThanas($district->thanas),
            ];
        }
        return $data;
    }

    private function getThanas($thanas) {
        $data = [];
        foreach ($thanas as $thana){
            $data[] = [
                "id" => $thana->id,
                "name" => $thana->name,
                "bn_name" => $thana->bn_name,
            ];
        }
        return $data;
    }
}