<?php

namespace App\Repositories;


class CategoryRepository {


    /**
     * Send children with services for a category
     * @param $category
     * @return mixed
     */
    public function childrenWithServices($category)
    {
        $children = $category->children()->select('id', 'name', 'thumb', 'banner')
            ->with(['services' => function ($query)
            {
                $query->select('id', 'category_id', 'name', 'thumb', 'banner', 'variable_type', 'variables');
            }])
            ->get();
        foreach ($children as $child)
        {
            array_add($child, 'slug_child_category', str_slug($child->name, '-'));
            foreach ($services = $child->services as $service)
            {
                if ($service->variable_type == 'Fixed')
                {
                    $price = (json_decode($service->variables)->price);
                    array_add($service, 'price', $price);
                }
                if ($service->variable_type == 'Options')
                {
                    $prices = (array)(json_decode($service->variables)->prices);
                    $min = (min($prices));
                    array_add($service, 'price', $min);
                }
                array_add($service, 'slug_service', str_slug($service->name, '-'));
                // review count of this partner for this service
                $review = $service->reviews()->where('review', '<>', '')->count('review');
                //avg rating of the partner for this service
                $rating = $service->reviews()->where('service_id', $service->id)->avg('rating');
                array_add($service, 'review', $review);
                array_add($service, 'rating', $rating);
                array_forget($service, 'variables');
            }
        }
        return $children;
    }

}