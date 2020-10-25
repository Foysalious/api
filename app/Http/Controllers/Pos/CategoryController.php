<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Partner;
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

            $partner_categories = $this->getPartnerCategories($partner);

            $master_categories = PosCategory::whereIn('id',$partner_categories)->select($this->getSelectColumnsOfCategory())->get()
                ->load(['children' => function ($q) use ($request, $service_where_query, $updated_after_clause,$deleted_after_clause) {
                    $q->whereHas('services', $service_where_query)
                        ->with(['services' => function ($service_query) use ($service_where_query, $updated_after_clause) {
                            $service_query->where($service_where_query);

                            $service_query->with(['discounts' => function ($discounts_query) use ($updated_after_clause) {
                                $discounts_query->runningDiscounts()
                                    ->select($this->getSelectColumnsOfServiceDiscount());

                                $discounts_query->where($updated_after_clause);
                            }])->select($this->getSelectColumnsOfService())->orderBy('name', 'asc');

                        }]);

                    if ($request->has('updated_after') ) {
                        $q->with(['deletedServices' => $deleted_after_clause]);
                    }
                }]);

            $all_services = [];
            $deleted_services = [];

            $master_categories->each(function ($category) use(&$all_services,&$deleted_services) {
                $category->children->each(function($child) use(&$all_services,&$deleted_services){

                    array_push($all_services,$child->services->all());
                    array_push($deleted_services,$child->deletedServices->all());
                });
                removeRelationsAndFields($category);
                $all_services = array_merge(... $all_services);
                $deleted_services = array_merge(... $deleted_services);
                $category->setRelation('services',collect($all_services));
                $category->setRelation('deleted_services',collect($deleted_services));
                $all_services = [];
                $deleted_services = [];
            });

            $items_with_buying_price = 0;
            $master_categories->each(function ($category) use (&$total_items, &$total_buying_price, &$items_with_buying_price) {
                $category->services->each(function ($service) use (&$total_items, &$total_buying_price,  &$items_with_buying_price) {
                    $service->unit          = $service->unit ? constants('POS_SERVICE_UNITS')[$service->unit] : null;
                    $service->warranty_unit = $service->warranty_unit ? config('pos.warranty_unit')[$service->warranty_unit] : null;
                    $total_items++;
                    if($service->cost) $items_with_buying_price += 1;
                    $total_buying_price += $service->cost * $service->stock;
                });
            });

            $data                       = [];
            $data['categories']         = $master_categories;
            $data['total_items']        = (double)$total_items;
            $data['total_buying_price'] = (double)$total_buying_price;
            $data['items_with_buying_price'] = $items_with_buying_price;

            return api_response($request, $master_categories, 200, $data);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getPartnerCategories(Partner $partner)
    {
        $masters = [];
        $children = array_unique($partner->posServices()->published()->pluck('pos_category_id')->toArray());
        PosCategory::whereIn('id',$children)->get()->each(function($child) use(&$masters){
            if($child->parent()->first())
                array_push($masters,$child->parent()->first()->id);
        });
        return array_unique($masters);

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

    /**
     * @param Request $request
     * @param $partner
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMasterCategories(Request $request, $partner)
    {
        try {
            $data = [];
            $partner = $request->partner;
            $master_categories = $partner->posCategories()->get();

            if (!$master_categories) return api_response($request, null, 404);

            $data['total_category'] = count($master_categories);
            $data['categories'] = [];

            foreach ($master_categories as $master_category) {
                $category = $master_category->category()->first();
                $item['name'] = $category->name;
                $total_services = 0;
                $category->children()->get()->each(function ($child) use ($partner, &$total_services) {
                    $total_services += $child->services()->where('partner_id', $partner->id)->where('publication_status', 1)->count();
                });
                $item['total_items'] = $total_services;
                array_push($data['categories'], $item);
            }

            return api_response($request, $master_categories, 200, ['data' => $data]);

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
