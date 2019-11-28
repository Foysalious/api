<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PosCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $partner            = $request->partner;
            $total_items        = 0.00;
            $total_buying_price = 0.00;

            $updated_after_clause = function ($q) use ($request) {
                if ($request->has('updated_after')) {
                    $q->where('updated_at', '>=', $request->updated_after);
                }
            };
            $deleted_after_clause = function ($q) use ($request, $partner) {
                if ($request->has('updated_after')) {
                    $q->select('id','partner_id','pos_category_id')->partner($partner->id)
                        ->where('deleted_at', '>=', $request->updated_after);
                }
            };
            $service_where_query  = function ($service_query) use ($partner, $updated_after_clause, $request) {
                $service_query->partner($partner->id);

                if ($request->has('updated_after')) {
                    $service_query->where(function ($service_where_group_query) use ($updated_after_clause) {
                        $service_where_group_query->where($updated_after_clause)
                            ->orWhereHas('discounts', function ($discounts_query) use ($updated_after_clause) {
                                $discounts_query->where($updated_after_clause);
                            });
                    });
                }
            };

            $sub_categories = PosCategory::child()->published()
                ->whereHas('services', $service_where_query)
                ->with(['services' => function ($service_query) use ($service_where_query, $updated_after_clause) {
                    $service_query->where($service_where_query);

                    $service_query->with(['discounts' => function ($discounts_query) use ($updated_after_clause) {
                        $discounts_query->runningDiscounts()
                            ->select($this->getSelectColumnsOfServiceDiscount());

                        $discounts_query->where($updated_after_clause);
                    }])->select($this->getSelectColumnsOfService());

                }])->select($this->getSelectColumnsOfCategory());
            if ($request->has('updated_after')) {
                $sub_categories->with(['deletedServices' => $deleted_after_clause]);
            }
            $sub_categories = $sub_categories->get();
            if (!$sub_categories) return api_response($request, null, 404);

            $sub_categories->each(function ($category) use (&$total_items, &$total_buying_price) {
                $category->services->each(function ($service) use (&$total_items, &$total_buying_price) {
                    $service->unit          = $service->unit ? constants('POS_SERVICE_UNITS')[$service->unit] : null;
                    $service->warranty_unit = $service->warranty_unit ? config('pos.warranty_unit')[$service->warranty_unit] : null;
                    $total_items++;
                    $total_buying_price += $service->cost * $service->stock;
                });
            });

            $data                       = [];
            $data['categories']         = $sub_categories;
            $data['total_items']        = (double)$total_items;
            $data['total_buying_price'] = (double)$total_buying_price;

            return api_response($request, $sub_categories, 200, $data);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getSelectColumnsOfServiceDiscount()
    {
        return ['id', 'partner_pos_service_id', 'amount', 'is_amount_percentage', 'cap', 'start_date', 'end_date'];
    }

    private function getSelectColumnsOfService()
    {
        return [
            'id', 'partner_id', 'pos_category_id', 'name', 'publication_status', 'is_published_for_shop',
            'thumb', 'banner', 'app_thumb', 'app_banner', 'cost', 'price', 'wholesale_price', 'vat_percentage', 'stock', 'unit', 'warranty', 'warranty_unit', 'show_image', 'shape', 'color'
        ];
    }

    private function getSelectColumnsOfCategory()
    {
        return ['id', 'name', 'thumb', 'banner', 'app_thumb', 'app_banner'];
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
}
