<?php

namespace Sheba\Service;

use Sheba\Dal\Category\Category;

class CategoryDeeplinkGenerator
{
    public function getDeeplink(Category $category, $withZxing = true)
    {
        $zxing_text = 'zxing://';
        $android = "host_sub-category/?url=".config('sheba.front_url') . "sub-category/" . $category->id;
        $ios = "host_sub-category/?url=".config('sheba.front_url') . "sub-category/" . $category->id;
        return [
            'android' => $withZxing ? $zxing_text.$android : $android,
            'ios' => $withZxing ? $zxing_text.$ios : $ios,
            'sub_link' => "/?url=".config('sheba.front_url') . "sub-category/" . $category->id
        ];
    }
}