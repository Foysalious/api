<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PosCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $sub_categories = PosCategory::child()->published()->select($this->getSelectColumnsOfCategory())->get();
            if (!$sub_categories) return api_response($request, null, 404);
            return api_response($request, $sub_categories, 200, ['categories' => $sub_categories]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getMasterCategoriesWithSubCategory(Request $request)
    {
        try {
            $master_categories = PosCategory::with(['children' => function ($query) {
                $query->select(array_merge($this->getSelectColumnsOfCategory(), ['parent_id']));
            }])->parents()->published()->select($this->getSelectColumnsOfCategory())->get();

            if (!$master_categories) return api_response($request, null, 404);

            return api_response($request, $master_categories, 200, ['categories' => $master_categories]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getSelectColumnsOfCategory()
    {
        return ['id', 'name', 'thumb', 'banner', 'app_thumb', 'app_banner'];
    }
}
