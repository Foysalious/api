<?php namespace Sheba\Partner\DataMigration;


use App\Models\Partner;
use App\Sheba\InventoryService\InventoryServerClient;
use Illuminate\Support\Collection;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategoryRepository;
use Sheba\Dal\PartnerPosService\PartnerPosServiceRepository;
use Sheba\Dal\PosCategory\PosCategoryRepository;
use Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToInventoryJob;
use Sheba\Pos\Repositories\PosServiceDiscountRepository;
use Sheba\Pos\Repositories\PosServiceLogRepository;
use Sheba\Repositories\PartnerRepository;
use DB;

class InventoryDataMigration
{
    const CHUNK_SIZE = 10;

    /** @var Partner */
    private $partner;
    /** @var PosCategoryRepository */
    private $posCategoryRepository;
    /** @var PartnerPosCategoryRepository */
    private $partnerPosCategoryRepository;
    /** @var Collection */
    private $categories;
    /** @var void */
    private $posCategories;
    /**  @var InventoryServerClient */
    private $client;
    /** @var PartnerPosServiceRepository */
    private $partnerPosServiceRepository;
    private $partnerPosServiceIds;
    /** @var PosServiceLogRepository */
    private $posServiceLogRepository;
    /** @var PosServiceDiscountRepository */
    private $posServiceDiscountRepository;
    /** @var PartnerRepository */
    private $partnerRepository;

    public function __construct(PosCategoryRepository $posCategoryRepository,
                                PartnerPosCategoryRepository $partnerPosCategoryRepository,
                                InventoryServerClient $client,
                                PartnerPosServiceRepository $partnerPosServiceRepository,
                                PosServiceLogRepository $posServiceLogRepository,
                                PosServiceDiscountRepository $posServiceDiscountRepository,
                                PartnerRepository $partnerRepository)
    {
        $this->posCategoryRepository = $posCategoryRepository;
        $this->partnerPosCategoryRepository = $partnerPosCategoryRepository;
        $this->partnerPosServiceRepository = $partnerPosServiceRepository;
        $this->posServiceLogRepository = $posServiceLogRepository;
        $this->posServiceDiscountRepository = $posServiceDiscountRepository;
        $this->partnerRepository = $partnerRepository;
        $this->client = $client;
        $this->categories = collect();
    }

    /**
     * @param mixed $partner
     * @return InventoryDataMigration
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param void $posCategories
     * @return InventoryDataMigration
     */
    private function setPosCategories($posCategories)
    {
        $this->posCategories = $posCategories;
        return $this;
    }

    private function generatePosCategoriesData($pos_category)
    {
        if ($pos_category->parent_id && !$this->categories->contains('id', $pos_category->parent_id)) {
            $parent_category = $this->posCategories->find($pos_category->parent_id);
            $this->generatePosCategoriesData($parent_category);
            $this->categories->push($pos_category);
        } else {
            $this->categories->push($pos_category);
        }
    }

    public function migrate()
    {
        $this->migratePartner();
        $this->migrateCategories();
        $this->migrateCategoryPartner();
        $this->migrateProducts();
        $this->migrateProductsImages();
        $this->migrateProductUpdateLogs();
        $this->migrateDiscounts();
    }

    private function migratePartner()
    {
        dispatch(new PartnerDataMigrationToInventoryJob($this->partner, ['partner_info' => $this->generatePartnerMigrationData()]));
    }

    private function migrateCategories()
    {
        $chunks = array_chunk($this->generatePosCategoriesMigrationData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToInventoryJob($this->partner, ['pos_categories' => $chunk]));
        }
    }

    private function migrateCategoryPartner()
    {
        $chunks = array_chunk($this->generatePartnerPosCategoriesMigrationData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToInventoryJob($this->partner, ['partner_pos_categories' => $chunk]));
        }
    }

    private function migrateProducts()
    {
        $chunks = array_chunk($this->generatePartnerPosServicesMigrationData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToInventoryJob($this->partner, ['products' => $chunk]));
        }
    }

    private function migrateProductsImages()
    {
        $chunks = array_chunk($this->generatePartnerPosServiceImageGalleryData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToInventoryJob($this->partner, ['partner_pos_service_image_gallery' => $chunk]));
        }
    }

    private function migrateProductUpdateLogs()
    {
        $chunks = array_chunk($this->generatePartnerPosServiceLogsMigrationData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToInventoryJob($this->partner, ['partner_pos_services_logs' => $chunk]));
        }
    }

    private function migrateDiscounts()
    {
        $chunks = array_chunk($this->generatePartnerPosServiceDiscountsMigrationData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToInventoryJob($this->partner, ['partner_pos_service_discounts' => $chunk]));
        }
    }

    private function generatePartnerMigrationData()
    {
        return [
            'id' => $this->partner->id,
            'sub_domain' => $this->partner->sub_domain,
            'vat_percentage' => $this->partner->posSetting ? $this->partner->posSetting->vat_percentage : 0.0
        ];
    }

    private function generatePosCategoriesMigrationData()
    {
        $partner_pos_categories = $this->partnerPosCategoryRepository->getPartnerPosCategoriesForMigration($this->partner->id)->toArray();
        $pos_categories_ids = array_unique(array_column($partner_pos_categories, 'category_id'));
        $this->setPosCategories($this->posCategoryRepository->getPosCategoriesForMigration($pos_categories_ids));
        $this->posCategories->each(function ($pos_category) {
            if (!$this->categories->contains('id', $pos_category->id)) $this->generatePosCategoriesData($pos_category);
        });
        return $this->categories->toArray();
    }

    private function generatePartnerPosCategoriesMigrationData()
    {
        return $this->partnerPosCategoryRepository->getPartnerPosCategoriesForMigration($this->partner->id)->toArray();
    }

    private function generatePartnerPosServicesMigrationData()
    {
        $partner_pos_services = $this->partnerPosServiceRepository->where('partner_id', $this->partner->id);
        $this->partnerPosServiceIds = $partner_pos_services;
        $products = $partner_pos_services->withTrashed()->select('id', 'partner_id', 'pos_category_id AS category_id',
            'name', 'app_thumb', 'description', 'cost', 'price', 'unit', 'wholesale_price', 'stock', 'warranty', 'warranty_unit',
            'vat_percentage', 'publication_status', 'is_published_for_shop', 'created_by_name', 'updated_by_name',
            'created_at', 'updated_at', 'deleted_at')->get()->toArray();
        $this->partnerPosServiceIds = array_column($products, 'id');
        return $products;
    }

    private function generatePartnerPosServiceImageGalleryData()
    {
        return DB::table('partner_pos_service_image_gallery')
            ->whereIn('partner_pos_service_id', $this->partnerPosServiceIds)
            ->select('partner_pos_service_id AS product_id', 'image_link', 'created_by_name', 'created_at',
                'updated_by_name', 'updated_at')->get();
    }

    private function generatePartnerPosServiceLogsMigrationData()
    {
        return DB::table('partner_pos_service_logs')
            ->whereIn('partner_pos_service_id', $this->partnerPosServiceIds)
            ->select('partner_pos_service_id AS product_id', 'field_names', 'old_value', 'new_value',
                'created_by_name', 'created_at')->get();
    }

    private function generatePartnerPosServiceDiscountsMigrationData()
    {
        return $this->posServiceDiscountRepository
            ->whereIn('partner_pos_service_id', $this->partnerPosServiceIds)
            ->select('partner_pos_service_id AS type_id', DB::raw("'product' AS type"), 'amount',
                'is_amount_percentage', 'cap', 'start_date', 'end_date', 'created_by_name', 'updated_by_name',
                'created_at', 'updated_at')->get()->toArray();
    }
}