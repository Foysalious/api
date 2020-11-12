<?php namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class DivisionsWithDistrictsTransformer extends TransformerAbstract
{
    public function transform($divisions)
    {
        return [
            'divisions'     => $this->getFormattedData($divisions)
        ];
    }

    private function getFormattedData($divisions)
    {
        $data = [];
        foreach ($divisions as $division){
            $data[] =  [
                "id" => $division->id,
                "name" => $division->name,
                "bn_name" => $division->bn_name,
                "districts" => $this->getDistricts($division->districts),
            ];
        }

        return $data;
    }

    private function getDistricts($districts)
    {
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

    private function getThanas($thanas)
    {
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