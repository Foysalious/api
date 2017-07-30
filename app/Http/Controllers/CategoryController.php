<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Repositories\CategoryRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Tinify\Tinify;
class CategoryController extends Controller
{

    private $categoryRepository;
    private $serviceRepository;
    private $tinify;

    public function __construct()
    {
        $this->tinify=\Tinify\setKey(env(''));
        $this->categoryRepository = new CategoryRepository();
        $this->serviceRepository = new ServiceRepository();
    }


    /**
     * Get all categories
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $categories = Category::parents()
            ->select('id', 'name', 'thumb', 'banner')
            ->get();
        foreach ($categories as $category) {
            array_add($category, 'slug', str_slug($category->name, '-'));
        }
        return count($categories) > 0 ? response()
            ->json(['categories' => $categories, 'msg' => 'successful', 'code' => 200]) : response()->json(['msg' => 'nothing found', 'code' => 404]);
    }

    /**
     * Get children of a category with services
     * @param Category $category
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChildren($category, Request $request)
    {
        $category = Category::find($category);
        if ($category != null) {
            $children = $this->categoryRepository->childrenWithServices($category, $request);
            $cat = collect($category)->only(['name', 'banner']);
            if (count($children) > 0)
                return response()->json(['category' => $cat, 'secondary_categories' => $children, 'msg' => 'successful', 'code' => 200]);
            else
                return response()->json(['msg' => 'no secondary categories found!', 'code' => 404]);
        } else {
            return response()->json(['msg' => 'category not found', 'code' => 404]);
        }
    }


    /**
     * Get parent of a category
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParent($category)
    {
        $category = Category::find($category);
        $parent = $category->parent()->select('id', 'name', 'thumb', 'banner')->first();
        if ($parent)
            return response()->json(['parent' => $parent, 'msg' => 'successful', 'code' => 200]);
        return response()->json(['msg' => 'no parent found', 'code' => 404]);
    }

    public function getServices($category, Request $request)
    {
        $category = Category::where([
            ['id', $category],
            ['publication_status', 1]
        ])->first();
        if ($category != null) {
            $cat = collect($category)->only(['name', 'banner']);

            if ($category->parent == null) {
                $services = $this->categoryRepository->getChildrenServices($category, $request);
                return response()->json(['category' => $cat, 'services' => $services, 'msg' => 'successful', 'code' => 200]);
            };
            array_add($cat, 'parent', collect($category->parent)->only(['id', 'name']));
            $category = Category::with(['services' => function ($q) {
                $q->select('id', 'category_id', 'name', 'thumb', 'banner', 'variable_type', 'variables')->where('publication_status', 1);
            }])->where([
                ['id', $category->id],
                ['publication_status', 1]
            ])->first();
            $services = $this->serviceRepository->addServiceInfo($category->services, $request->location);
            return response()->json(['category' => $cat, 'services' => $services, 'msg' => 'successful', 'code' => 200]);
        } else {
            return response()->json(['msg' => 'category not found', 'code' => 404]);
        }
    }
}
