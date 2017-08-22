<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;

class CategoryServiceController extends Controller
{
    private $serviceRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
    }

    /**
     *  Category wise Service Tree
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategoryServices()
    {
        $category_services = Category::parents()->with(['children' => function ($query) {
            $query->with(['services' => function ($query) {
                $query->select('id', 'name', 'slug', 'category_id')->where('publication_status', 1);
            }])->select('id', 'name', 'parent_id');
        }])->select('id', 'name')->get();
        if (!$category_services->isEmpty()) {
            return response()->json(['category_services' => $category_services, 'msg' => 'successful', 'code' => 200]);
        } else {
            return response()->json(['msg' => 'no service found', 'code' => 404]);
        }
    }

}
