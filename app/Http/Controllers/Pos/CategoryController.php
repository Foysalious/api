<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PosCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;
use App\Sheba\Pos\Category\Category;
use Sheba\Dal\PartnerPosServiceBatch\PartnerPosServiceBatchRepositoryInterface;
use Sheba\Dal\PartnerPosServiceBatch\Model as PosServiceBatch;
use Illuminate\Support\Facades\DB;


class CategoryController extends Controller
{
    public function index(Request $request)
    {
        ini_set('memory_limit', '2048M');
        try {
            $partner = $request->partner;
            $total_items = 0.00;
            $total_buying_price = 0.00;

            $updated_after_clause = function ($q) use ($request) {
                if ($request->has('updated_after')) {
                    $q->where('updated_at', '>=', $request->updated_after);
                }
            };
            $deleted_after_clause = function ($q) use ($request, $partner) {
                if ($request->has('updated_after')) {
                    $q->where('deleted_at', '>=', $request->updated_after);
                }
            };
            $service_where_query = function ($service_query) use ($partner, $updated_after_clause, $request) {
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

            $deleted_service_where_query = function ($deleted_service_query) use ($partner, $updated_after_clause, $request) {
                $deleted_service_query->partner($partner->id);
            };

            $partner_categories = $this->getPartnerCategories($partner);

            $master_categories = PosCategory::whereIn('id', $partner_categories)->select($this->getSelectColumnsOfCategory())->get()
                ->load(['children' => function ($q) use ($request, $service_where_query, $deleted_service_where_query, $updated_after_clause, $deleted_after_clause) {
                    $q->whereHas('services', $service_where_query)
                        ->with(['services' => function ($service_query) use ($service_where_query, $updated_after_clause) {
                            $service_query->where($service_where_query);

                            $service_query->with(['discounts' => function ($discounts_query) use ($updated_after_clause) {
                                $discounts_query->runningDiscounts()
                                    ->select($this->getSelectColumnsOfServiceDiscount());

                                $discounts_query->where($updated_after_clause);
                            }])->select($this->getSelectColumnsOfService())->orderBy('name', 'asc');

                        }]);
                    if ($request->has('updated_after')) {
                        $q->orWhereHas('deletedServices', $deleted_service_where_query)->with(['deletedServices' => function ($deleted_service_query) use ($deleted_after_clause, $deleted_service_where_query) {
                            $deleted_service_query->where($deleted_after_clause)->where($deleted_service_where_query)->select($this->getSelectColumnsOfDeletedService());
                        }]);
                    }
                }]);

            $all_services = [];
            $deleted_services = [];

            $master_categories->each(function ($category) use ($request, &$all_services, &$deleted_services) {
                $category->children->each(function ($child) use ($request, &$children, &$all_services, &$deleted_services) {
                    array_push($all_services, $child->services->all());
                    array_push($deleted_services, $child->deletedServices->all());
                });
                removeRelationsAndFields($category);
                if (!empty($all_services)) $all_services = array_merge(... $all_services);
                if (!empty($deleted_services)) $deleted_services = array_merge(... $deleted_services);
                $category->setRelation('services', collect($all_services));
                if ($request->has('updated_after')) {
                    $category->setRelation('deletedServices', collect($deleted_services));
                }
                $all_services = [];
                $deleted_services = [];
            });

            $master_categories->each(function ($category) use (&$category_id, &$total_items, &$total_buying_price, &$items_with_buying_price) {
                $category_id = $category->id;
                $category->total_services = count($category->services);
                $category->services->each(function ($service) use ($category_id, &$total_items, &$total_buying_price, &$items_with_buying_price) {
                    $service->pos_category_id = $category_id;
                    $service->unit = $service->unit ? constants('POS_SERVICE_UNITS')[$service->unit] : null;
                    $service->warranty_unit = $service->warranty_unit ? config('pos.warranty_unit')[$service->warranty_unit] : null;
                    $service->stock = $service->batches->sum('stock');
                    $service->image_gallery = $service->imageGallery ? $service->imageGallery->map(function($image){
                        return [
                            'id' =>   $image->id,
                            'image_link' => $image->image_link
                        ];
                    }) : [];
                    $total_items++;
                    if ($service->cost) $items_with_buying_price++;
                    $total_buying_price += $this->getBuyingPriceOfService($service);
                });
            });

            if ($request->has('updated_after')) {
                $master_categories = $master_categories->filter(function ($master_category) {
                    return ($master_category->services->count() > 0) || ($master_category->deletedServices->count() > 0);
                });
            }



            $data = [];
            $data['categories'] = $master_categories->values()->all();
            $data['total_items'] = (double)$total_items;
            $data['total_buying_price'] = (double)$total_buying_price;
            $data['items_with_buying_price'] = $items_with_buying_price;
            $data['is_webstore_published'] = $partner->is_webstore_published;

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
    private function getSelectColumnsOfDeletedService()
    {
        return [
            'id','partner_id','pos_category_id'
        ];
    }

    private function getSelectColumnsOfCategory()
    {
        return ['pos_categories.id', 'name', 'thumb', 'banner', 'app_thumb', 'app_banner','is_published_for_sheba'];
    }

    public function getMasterCategoriesWithSubCategory(Request $request)
    {
        try {
            $partner_id = $request->partner->id;
            $master_categories = PosCategory::where(function ($q) use ($partner_id) {
                $q->where('is_published_for_sheba', 1)->orWhere(function ($q) use ($partner_id) {
                    $q->where('is_published_for_sheba', 0)->whereHas('partnerPosCategory', function ($q) use ($partner_id) {
                        $q->where('partner_id', $partner_id);
                    });
                });
            })->with(['children' => function ($query) {
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
     * @return JsonResponse
     */
    public function getMasterCategories(Request $request, $partner)
    {
        try {
            $data = [];
            $partner = $request->partner;
            $master_categories = PartnerPosCategory::byMasterCategoryByPartner($partner->id)->get();

            if (!$master_categories) return api_response($request, null, 404);

            $data['total_category'] = count($master_categories);
            $data['categories'] = [];
            foreach ($master_categories as $master_category) {
                $category = $master_category->category()->first();
                $item['id'] = $category->id;
                $item['name'] = $category->name;
                $item['is_published_for_sheba'] = $category->is_published_for_sheba;
                $total_services = 0;
                $category->children()->get()->each(function ($child) use ($partner, &$total_services) {
                    $total_services += $child->services()->where('partner_id', $partner->id)->where('publication_status', 1)->count();
                });
                $item['total_items'] = $total_services;
                array_push($data['categories'], $item);
            }

            return api_response($request, null, 200, ['data' => $data]);

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param $partner
     * @param Category $category
     * @return JsonResponse
     */
    public function store(Request $request, $partner, Category $category)
    {
        $this->validate($request, [
            'name' => 'required|string',
        ]);
        $partner = $request->partner;
        $modifier = $request->manager_resource;
        list($master_category, $sub_category) = $category->createCategory($modifier, $request->name);
        $category->createPartnerCategory($partner->id, $master_category, $sub_category);
        return api_response($request, null, 200, ['message' => 'Category Created Successfully']);
    }

    /**
     * @param Request $request
     * @param $partner
     * @param $category_id
     * @param Category $category
     * @return JsonResponse
     */
    public function update(Request $request, $partner, $category_id, Category $category)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $modifier = $request->manager_resource;
        $pos_category = PosCategory::where('id', $request->category_id)->first();
        if ($pos_category->is_published_for_sheba)
            return api_response($request, null, 403, ['message' => 'Not allowed to update this category']);
        $category->update($modifier, $pos_category, $request->name);
        return api_response($request, null, 200, ['message' => 'Category Updated Successfully']);
    }

    public function getBuyingPriceOfService($service)
    {
        /** @var Partner $partner */
        $partner = $service->partner;
        if($partner->isMigratedToAccounting()) {
            $batches = $this->getBatches($service->id);
            $total_buying_price = 0.0;
            foreach ($batches as $batch) {
                $total_buying_price += $batch->cost * $batch->stock;
            }
            return $total_buying_price;
        }
        return $service->cost * $service->stock;

    }

    public function getBatches($service_id)
    {
        /** @var PartnerPosServiceBatchRepositoryInterface $partnerPosServiceBatchRepository */
        $partnerPosServiceBatchRepository = app(PartnerPosServiceBatchRepositoryInterface::class);
        return $partnerPosServiceBatchRepository->where('partner_pos_service_id', $service_id)->get();
    }
}
