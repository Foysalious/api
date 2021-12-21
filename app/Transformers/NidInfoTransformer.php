<?php namespace App\Transformers;

use Sheba\Gender\Gender;
use League\Fractal\TransformerAbstract;

class NidInfoTransformer extends TransformerAbstract
{
    public function transform($data)
    {
        return [
            'name' => isset($data['name']) ? $data['name'] : null,
            'bn_name' => isset($data["bn_name"]) ? $data["bn_name"] : null,
            'nid_no' => isset($data["nid_no"]) ? $data["nid_no"] : null,
            'dob' => isset($data["dob"]) ? date_create($data["dob"])->format('Y-m-d') : null,
            'father_name' => isset($data["father_name"]) ? $data["father_name"] : null,
            'mother_name' => isset($data["mother_name"]) ? $data["mother_name"] : null,
            'blood_group' => isset($data["blood_group"]) ? $data["blood_group"] : null,
            'address' => isset($data["address"]) ? $data["address"] : null,
            'gender' => isset($data["gender"]) ? Gender::getGenderDisplayableName($data["gender"]) : null
        ];
    }
}
