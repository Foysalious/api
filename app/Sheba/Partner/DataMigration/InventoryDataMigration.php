<?php namespace Sheba\Partner\DataMigration;


use App\Models\Partner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategoryRepository;
use Sheba\Dal\PartnerPosService\PartnerPosServiceRepository;
use Sheba\Dal\PartnerPosServiceBatch\PartnerPosServiceBatchRepositoryInterface;
use Sheba\Dal\PosCategory\PosCategoryRepository;
use Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToInventoryJob;
use DB;

class InventoryDataMigration
{
    const CHUNK_SIZE = 10;
    private $currentQueue = 1;
    /** @var Partner */
    private $partner;
    /** @var PosCategoryRepository */
    private $posCategoryRepository;
    /** @var PartnerPosCategoryRepository */
    private $partnerPosCategoryRepository;
    /** @var Collection */
    private $categoriesData;
    /** @var void */
    private $posCategoriesData;
    /** @var PartnerPosServiceRepository */
    private $partnerPosServiceRepository;
    private $partnerPosServiceIds;
    private $partnerPosServiceImages;
    private $partnerPosServiceLogs;
    private $partnerPosServiceDiscounts;
    private $partnerPosCategories;
    private $partnerPosServices;
    private $posCategories;
    private $partnerPosServiceBatches;
    /**
     * @var PartnerPosServiceBatchRepositoryInterface
     */
    private $partnerPosServiceBatchRepository;
    private $queue_and_connection_name;
    private $shouldQueue;


    public function __construct(
        PosCategoryRepository                     $posCategoryRepository,
        PartnerPosCategoryRepository              $partnerPosCategoryRepository,
        PartnerPosServiceRepository               $partnerPosServiceRepository,
        PartnerPosServiceBatchRepositoryInterface $partnerPosServiceBatchRepository)
    {
        $this->posCategoryRepository = $posCategoryRepository;
        $this->partnerPosCategoryRepository = $partnerPosCategoryRepository;
        $this->partnerPosServiceRepository = $partnerPosServiceRepository;
        $this->partnerPosServiceBatchRepository = $partnerPosServiceBatchRepository;
        $this->categoriesData = collect();
    }

    /**
     * @param mixed $partner
     * @return InventoryDataMigration
     */
    public function setPartner(Partner $partner): InventoryDataMigration
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param $queue_and_connection_name
     * @return InventoryDataMigration
     */
    public function setQueueAndConnectionName($queue_and_connection_name)
    {
        $this->queue_and_connection_name = $queue_and_connection_name;
        return $this;
    }

    /**
     * @param mixed $shouldQueue
     * @return InventoryDataMigration
     */
    public function setShouldQueue($shouldQueue)
    {
        $this->shouldQueue = $shouldQueue;
        return $this;
    }


    private function generatePosCategoriesData($pos_category)
    {
        if (($pos_category && $pos_category->parent_id) && !$this->categoriesData->contains('id', $pos_category->parent_id)) {
            $parent_category = $this->posCategoriesData->find($pos_category->parent_id);
            if ($parent_category) $this->generatePosCategoriesData($parent_category);
        }
        $this->categoriesData->push($pos_category);
    }

    public function migrate()
    {
        $this->generateMigrationData();
        $this->migrateCategories($this->posCategories);
        $this->migrateCategoryPartner($this->partnerPosCategories);
        $this->migrateProducts($this->partnerPosServices);
    }

    private function generateMigrationData()
    {
        $this->posCategories = $this->generatePosCategoriesMigrationData();
        $this->partnerPosCategories = $this->generatePartnerPosCategoriesMigrationData();
        $this->partnerPosServices = $this->generatePartnerPosServicesMigrationData();
        $this->partnerPosServiceBatches = collect($this->generatePartnerPosServiceBatchesData());
        $this->partnerPosServiceImages = collect($this->generatePartnerPosServiceImageGalleryData());
        $this->partnerPosServiceLogs = collect($this->generatePartnerPosServiceLogsMigrationData());
        $this->partnerPosServiceDiscounts = collect($this->generatePartnerPosServiceDiscountsMigrationData());
    }

    private function migrateCategories($data)
    {
        $chunks = array_chunk($data, self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            $this->setRedisKey();
            $this->shouldQueue ? dispatch(new PartnerDataMigrationToInventoryJob($this->partner, ['pos_categories' => $chunk], $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue)) :
                dispatchJobNow(new PartnerDataMigrationToInventoryJob($this->partner, ['pos_categories' => $chunk], $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue));
            $this->increaseCurrentQueueValue();
        }
    }

    private function migrateCategoryPartner($data)
    {
        $chunks = array_chunk($data, self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            $this->setRedisKey();
            $this->shouldQueue ? dispatch(new PartnerDataMigrationToInventoryJob($this->partner, ['partner_pos_categories' => $chunk], $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue)) :
                dispatchJobNow(new PartnerDataMigrationToInventoryJob($this->partner, ['partner_pos_categories' => $chunk], $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue));
            $this->increaseCurrentQueueValue();
        }
    }

    private function migrateProducts($data)
    {
        $chunks = array_chunk($data, 1);
        foreach ($chunks as $chunk) {
            $productIds = array_column($chunk, 'id');
            $searchCatIds = array_column($chunk, 'category_id');
            $allCategoryIds = array_merge(array_column($this->posCategories, 'id'), array_column($this->partnerPosCategories, 'category_id'));
            $diff = array_diff($searchCatIds, $allCategoryIds);
            $notTaggedCategories = !empty($diff) ? $this->getNotTaggedCategories($diff)->first()->toArray() : [];
            list($images, $logs, $discounts, $batches) = $this->getProductsRelatedData($productIds);
            $this->setRedisKey();
            $this->shouldQueue ? dispatch(new PartnerDataMigrationToInventoryJob($this->partner, [
                'products' => $chunk,
                'partner_pos_service_batches' => $batches,
                'partner_pos_service_image_gallery' => $images,
                'partner_pos_services_logs' => $logs,
                'partner_pos_service_discounts' => $discounts,
                'not_tagged_categories' => $notTaggedCategories,
            ], $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue)) :
                dispatchJobNow(new PartnerDataMigrationToInventoryJob($this->partner, [
                    'products' => $chunk,
                    'partner_pos_service_batches' => $batches,
                    'partner_pos_service_image_gallery' => $images,
                    'partner_pos_services_logs' => $logs,
                    'partner_pos_service_discounts' => $discounts,
                    'not_tagged_categories' => $notTaggedCategories,
                ], $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue));
            $this->increaseCurrentQueueValue();
        }
    }

    private function generatePosCategoriesMigrationData(): array
    {
        $partner_pos_categories = $this->partnerPosCategoryRepository->getPartnerPosCategoriesForMigration($this->partner->id)->toArray();
        $pos_categories_ids = array_unique(array_column($partner_pos_categories, 'category_id'));
        $this->posCategoriesData = $this->posCategoryRepository->getPosCategoriesForMigration($pos_categories_ids);
        $this->posCategoriesData->each(function ($pos_category) {
            if (!$this->categoriesData->contains('id', $pos_category->id)) $this->generatePosCategoriesData($pos_category);
        });
        return $this->categoriesData->toArray();
    }

    private function generatePartnerPosCategoriesMigrationData()
    {
        return $this->partnerPosCategoryRepository->getPartnerPosCategoriesForMigration($this->partner->id)->toArray();
    }

    private function generatePartnerPosServicesMigrationData()
    {
        $products = $this->partnerPosServiceRepository->where('partner_id', $this->partner->id)
            ->where(function ($q) {
                $q->where('is_migrated', null)->orWhere('is_migrated', 0);
            })->withTrashed()->select('id', 'partner_id', 'pos_category_id AS category_id', 'name', 'app_thumb',
                'description', 'price', 'unit', 'wholesale_price', 'warranty', 'warranty_unit', 'weight', 'weight_unit',
                'vat_percentage', 'publication_status', 'is_published_for_shop', 'created_by_name', 'updated_by_name',
                DB::raw('SUBTIME(created_at,"6:00:00") as created_at, 
                SUBTIME(updated_at,"6:00:00") as updated_at, 
                SUBTIME(deleted_at,"6:00:00") as deleted_at'))
            ->get()->toArray();
        $this->partnerPosServiceIds = array_column($products, 'id');
        return $products;
    }

    private function generatePartnerPosServiceBatchesData()
    {
        return $this->partnerPosServiceBatchRepository->builder()
            ->whereIn('partner_pos_service_id', $this->partnerPosServiceIds)
            ->withTrashed()->select('partner_pos_service_id AS product_id', 'supplier_id', 'from_account', 'cost', 'stock', 'created_by_name','updated_by_name',
                DB::raw('SUBTIME(created_at,"6:00:00") as created_at, SUBTIME(updated_at,"6:00:00") as updated_at, 
                SUBTIME(deleted_at,"6:00:00") as deleted_at'))
            ->get();
    }

    private function generatePartnerPosServiceImageGalleryData()
    {
        return DB::table('partner_pos_service_image_gallery')
            ->whereIn('partner_pos_service_id', $this->partnerPosServiceIds)
            ->whereNotNull('image_link')
            ->where('image_link', '<>', '')
            ->select('partner_pos_service_id AS product_id', 'image_link', 'created_by_name', 'created_at', 'updated_by_name',
                DB::raw('SUBTIME(created_at,"6:00:00") as created_at, SUBTIME(updated_at,"6:00:00") as updated_at'))
            ->get();
    }

    private function generatePartnerPosServiceLogsMigrationData()
    {
        return DB::table('partner_pos_service_logs')
            ->whereIn('partner_pos_service_id', $this->partnerPosServiceIds)
            ->select('partner_pos_service_id AS product_id', 'field_names', 'old_value', 'new_value','created_by_name',
                DB::raw('SUBTIME(created_at,"6:00:00") as created_at'))
            ->get();
    }

    private function generatePartnerPosServiceDiscountsMigrationData()
    {
        return DB::table('partner_pos_service_discounts')
            ->whereIn('partner_pos_service_id', $this->partnerPosServiceIds)
            ->select('partner_pos_service_id AS type_id', DB::raw("'sku_channel' AS type"), 'amount',
                'is_amount_percentage', 'cap', 'start_date', 'end_date', 'created_by_name', 'updated_by_name',
                DB::raw('SUBTIME(created_at,"6:00:00") as created_at, SUBTIME(updated_at,"6:00:00") as updated_at'))
            ->get();
    }

    private function getProductsRelatedData($productIds): array
    {
        $images = $this->partnerPosServiceImages->whereIn('product_id', $productIds)->values()->toArray();
        $logs = $this->partnerPosServiceLogs->whereIn('product_id', $productIds)->values()->toArray();
        $discounts = $this->partnerPosServiceDiscounts->whereIn('type_id', $productIds)->values()->toArray();
        $batches = $this->partnerPosServiceBatches->whereIn('product_id', $productIds)->values()->toArray();
        return [$images, $logs, $discounts, $batches];
    }

    private function setRedisKey()
    {
        $count = (int)Redis::get('PosOrderDataMigrationCount::' . $this->queue_and_connection_name);
        if ($count) $count++;
        else $count = 1;
        Redis::set('PosOrderDataMigrationCount::' . $this->queue_and_connection_name, $count);
        Redis::set('DataMigration::Partner::' . $this->partner->id . '::Inventory::Queue::' . $this->currentQueue, 'initiated');
    }

    private function increaseCurrentQueueValue()
    {
        $this->currentQueue += 1;
    }

    private function getNotTaggedCategories($diff)
    {
        return $this->posCategoryRepository->builder()->whereIn('id', $diff)->select('parent_id', 'name', 'thumb',
            'banner', 'app_thumb', 'app_banner', 'is_published_for_sheba','order',
            'icon', 'icon_png', DB::raw('(CASE 
                        WHEN name= "Others" THEN "1" 
                        ELSE "0" 
                        END) AS is_default'), 'created_by_name', 'updated_by_name',
            DB::raw('SUBTIME(created_at,"6:00:00") as created_at, SUBTIME(updated_at,"6:00:00") as updated_at')
        )->with('parent')->get();
    }
}