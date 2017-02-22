<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    private $categoryRepository;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
    }

    /**
     * Get all categories
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $categories = Category::parents()
            ->select('id', 'name', 'thumb', 'banner')
            ->get();
        foreach ($categories as $category) {
            array_add($category, 'slug_category', str_slug($category->name, '-'));
            $total_service = 0;
            foreach ($category->children as $child) {
                $total_service += $child->services()->count();
            }
            array_add($category, 'total_service', $total_service);
            array_forget($category, 'children');
        }
        if (!$categories->isEmpty())
            return response()
                ->json(['categories' => $categories, 'service_count' => Service::all()->count(), 'msg' => 'successful', 'code' => 200]);
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
        $cat = collect($category)->only(['name', 'banner']);
        if (!$children->isEmpty())
            return response()->json(['category' => $cat, 'children' => $children, 'msg' => 'successful', 'code' => 200]);
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

    public function getServices(Category $category)
    {
        $cat = collect($category)->only(['name', 'banner']);
        $category = Category::with(['services' => function ($q) {
            $q->select('id', 'category_id', 'name', 'thumb', 'banner', 'variable_type', 'variables')->where('publication_status', 1);
        }])->where([
            ['id', $category->id],
            ['publication_status', 1]
        ])->first();
        $services = $this->categoryRepository->addServiceInfo($category->services);
        return response()->json(['category' => $cat, 'services' => $services, 'msg' => 'successful', 'code' => 200]);
    }
}
