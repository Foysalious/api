<?php namespace App\Transformers;

use App\Models\Member;
use League\Fractal\TransformerAbstract;

class BusinessEmployeeDetailsTransformer extends TransformerAbstract
{
    /**
     * @param $members
     * @return array
     */
    public function transform($members)
    {
        return [
            'name' => "Nusrat Tabassum",
            'designation' => "Executive Officer",
            'mobile' => "+880 1678242900",
            'email' => "sadat@sheba.xyz",
            'profile_picture' => "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/profiles/pro_pic_1552910734_pro_pic_image_1.png",
            'department' => "Sales",
        ];
    }

}