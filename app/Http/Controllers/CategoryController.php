<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

use App\Http\Requests;

class CategoryController extends Controller {

    private $categoryRepository;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
    }

    /**
     * Get all categories
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Category::parents()
            ->select('id', 'name', 'thumb', 'banner')
            ->get();
        foreach ($categories as $category)
        {
            array_add($category, 'slug_category', str_slug($category->name, '-'));
            $total_service=0;
            foreach ($category->children as $child)
            {
                $total_service += $child->services()->count();
            }
            array_add($category, 'total_service', $total_service);
            array_forget($category,'children');
        }
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
        $children = $this->categoryRepository->childrenWithServices($category);

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
