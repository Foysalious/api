<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

use App\Http\Requests;

class CategoryController extends Controller {
    /**
     * Get all categories
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Category::parents()->select('id', 'name', 'thumb', 'banner')->get();
        if (!$categories->isEmpty())
            return response()->json(['categories' => $categories, 'msg' => 'successful', 'code' => 200]);
        return response()->json(['msg' => 'nothing found', 'code' => 404]);
    }

    /**
     * Get children of a category with services
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChildren(Category $category)
    {
        $children = $category->children()->select('id', 'name', 'thumb', 'banner')
            ->with(['services' => function ($query)
            {
                $query->select('id', 'category_id', 'name', 'thumb', 'banner', 'variable_type', 'variables');
            }])
            ->get();
        foreach ($children as $child)
        {
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
                    $max = (max($prices));
                    $min = (min($prices));
                    array_add($service, 'min_price', $min);
                    array_add($service, 'max_price', $max);
                }
                array_forget($service, 'variables');
            }
        }
        if (!$children->isEmpty())
            return response()->json(['children' => $children, 'msg' => 'successful', 'code' => 200]);
        return response()->json(['msg' => 'no children found', 'code' => 404]);
    }

    /**
     * Get parent of a category
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParent(Category $category)
    {
        $parent = $category->parent()->select('id', 'name', 'thumb', 'banner')->first();
        if ($parent)
            return response()->json(['parent' => $parent, 'msg' => 'successful', 'code' => 200]);
        return response()->json(['msg' => 'no parent found', 'code' => 404]);
    }
}
