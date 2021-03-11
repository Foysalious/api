<?php namespace Sheba\Partner\DataMigration;


use App\Models\Partner;
use App\Sheba\InventoryService\InventoryServerClient;
use Illuminate\Support\Collection;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategoryRepository;
use Sheba\Dal\PartnerPosService\PartnerPosServiceRepository;
use Sheba\Dal\PosCategory\PosCategoryRepository;
use Sheba\Partner\DataMigration\Jobs\InventoryDataMigrationJob;
use Sheba\Pos\Repositories\PosServiceLogRepository;
use DB;

class DataMigration
{
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

    /**
     * DataMigration constructor.
     * @param PosCategoryRepository $posCategoryRepository
     * @param PartnerPosCategoryRepository $partnerPosCategoryRepository
     * @param InventoryServerClient $client
     * @param PartnerPosServiceRepository $partnerPosServiceRepository
     * @param PosServiceLogRepository $posServiceLogRepository
     */
    public function __construct(PosCategoryRepository $posCategoryRepository,
                                PartnerPosCategoryRepository $partnerPosCategoryRepository,
                                InventoryServerClient $client,
                                PartnerPosServiceRepository $partnerPosServiceRepository,
                                PosServiceLogRepository $posServiceLogRepository)
    {
        $this->posCategoryRepository = $posCategoryRepository;
        $this->partnerPosCategoryRepository = $partnerPosCategoryRepository;
        $this->partnerPosServiceRepository = $partnerPosServiceRepository;
        $this->posServiceLogRepository = $posServiceLogRepository;
        $this->client = $client;
        $this->categories = collect();
    }

    /**
     * @param mixed $partner
     * @return DataMigration
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param void $posCategories
     * @return DataMigration
     */
    public function setPosCategories($posCategories)
    {
        $this->posCategories = $posCategories;
        return $this;
    }

    public function migrate()
    {
        $this->migrateInventoryData();
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

    private function migrateInventoryData()
    {
        $inventory_data = array_merge($this->generatePartnerMigrationData(), $this->generateCategoryMigrationData());
        $inventory_data = array_merge($inventory_data, $this->generateProductMigrationData());
        $inventory_data = array_merge($inventory_data, $this->generateProductUpdateLogsMigrationData());
        dispatch(new InventoryDataMigrationJob($this->partner->id, $inventory_data));
    }

    private function generatePartnerMigrationData()
    {
        $data = [];
        $data['partner_info'] = [
            'id' => $this->partner->id,
            'sub_domain' => $this->partner->sub_domain,
            'vat_percentage' => $this->partner->posSetting ? $this->partner->posSetting->vat_percentage : 0.0
        ];
        return $data;
    }

    private function generateCategoryMigrationData()
    {
        $partner_pos_categories = $this->partnerPosCategoryRepository
            ->getPartnerPosCategoriesForMigration($this->partner->id)->toArray();
        $pos_categories_ids = array_unique(array_column($partner_pos_categories, 'category_id'));
        $this->setPosCategories($this->posCategoryRepository->getPosCategoriesForMigration($pos_categories_ids));
        $this->posCategories->each(function ($pos_category) {
            if (!$this->categories->contains('id', $pos_category->id)) $this->generatePosCategoriesData($pos_category);
        });
        $categories = $this->categories->toArray();
        $data = [];
        $data['partner_pos_categories'] = $partner_pos_categories;
        $data['pos_categories'] = $categories;
        return $data;
    }

    private function generateProductMigrationData()
    {
        $partner_pos_services = $this->partnerPosServiceRepository->where('partner_id', $this->partner->id);
        $this->partnerPosServiceIds = $partner_pos_services;
        $data = [];
        $data['products'] = $partner_pos_services->select('id', 'partner_id', 'pos_category_id AS category_id',
            'name', 'description', 'cost', 'price', 'unit', 'wholesale_price', 'stock', 'warranty', 'warranty_unit',
            'vat_percentage', 'publication_status', 'is_published_for_shop', 'created_by_name', 'updated_by_name', 'created_at', 'updated_at')->get()->toArray();
        $this->partnerPosServiceIds = array_column($data['products'], 'id');
        return $data;
    }

    private function generateProductUpdateLogsMigrationData()
    {
        $data = [];
        $data['partner_pos_services_logs'] = DB::table('partner_pos_service_logs')->whereIn('partner_pos_service_id', $this->partnerPosServiceIds)
            ->select('partner_pos_service_id AS product_id', 'field_names', 'old_value', 'new_value',
                'created_by_name', 'created_at')->get();
        return $data;
    }
}