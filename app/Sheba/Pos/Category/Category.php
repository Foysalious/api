<?php namespace App\Sheba\Pos\Category;

use App\Models\PosCategory;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;
use Sheba\ModificationFields;
use Sheba\Pos\Category\Constants\CategoryConstants;

class Category
{
    use ModificationFields;

    /**
     * @param $modifier
     * @param $category_name
     * @return array
     */
    public function createCategory($modifier, $category_name)
    {
        $this->setModifier($modifier);
        $master_category_data = [
            'parent_id' => null,
            'name' => $category_name,
            'publication_status' => 1,
            'is_published_for_sheba' => 0,
        ];
        $master_category =  PosCategory::create($this->withCreateModificationField($master_category_data));
        $master_category_id = $master_category->id;

        $sub_category_data = [
            'parent_id' => $master_category_id,
            'name' => CategoryConstants::DEFAULT_SUB_CATEGORY_NAME,
            'publication_status' => 1,
            'is_published_for_sheba' => 0,
        ];

        $sub_category =  PosCategory::create($this->withCreateModificationField($sub_category_data));

        return [$master_category_id,$sub_category->id];
    }


    /**
     * @param $partner_id
     * @param $master_category_id
     * @param $sub_category_id
     * @return mixed
     */
    public function createPartnerCategory($partner_id, $master_category_id, $sub_category_id)
    {
        $data = [
            [
                'partner_id' => $partner_id,
                'category_id' => $master_category_id,
            ] + $this->modificationFields(true, false),
            [
                'partner_id' => $partner_id,
                'category_id' => $sub_category_id,
            ] + $this->modificationFields(true, false)

        ];

        return PartnerPosCategory::insert(($data));

    }

    /**
     * @param $modifier
     * @param $pos_category
     * @param $name
     * @return mixed
     */
    public function update($modifier, $pos_category, $name)
    {
        $this->setModifier($modifier);
        if($pos_category->name !=  $name)
        {
           return $pos_category->update($this->withUpdateModificationField(['name' => $name]));
        }
    }

}