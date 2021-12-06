<?php namespace App\Transformers\Business;

use Sheba\Dal\StickerCategory\StickerCategory;
use League\Fractal\TransformerAbstract;

class StickerCategoryList extends TransformerAbstract
{
    public function transform(StickerCategory $sticker_category)
    {
        return[
            'category_id' => $sticker_category->id,
            'category_name' => $sticker_category->name,
            'category_title' => $sticker_category->title,
            'stickers'=> $this->getStickers($sticker_category)
        ];
    }

    private function getStickers($sticker_category)
    {
        $all_sticker = [];
        $stickers = $sticker_category->stickers;
        foreach ($stickers as $sticker){
            array_push($all_sticker,[
                'id'=>$sticker->id,
                'image'=>$sticker->image,
            ]);
        }
       return $all_sticker;
    }
}