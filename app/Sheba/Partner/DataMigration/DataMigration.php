<?php namespace Sheba\Partner\DataMigration;


use App\Models\Partner;
use App\Sheba\InventoryService\InventoryServerClient;
use Illuminate\Support\Collection;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategoryRepository;
use Sheba\Dal\PosCategory\PosCategoryRepository;
use Sheba\Partner\DataMigration\Jobs\InventoryDataMigrationJob;

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

    /**
     * DataMigration constructor.
     * @param PosCategoryRepository $posCategoryRepository
     * @param PartnerPosCategoryRepository $partnerPosCategoryRepository
     * @param InventoryServerClient $client
     */
    public function __construct(PosCategoryRepository $posCategoryRepository,
                                PartnerPosCategoryRepository $partnerPosCategoryRepository,
                                InventoryServerClient $client)
    {
        $this->posCategoryRepository = $posCategoryRepository;
        $this->partnerPosCategoryRepository = $partnerPosCategoryRepository;
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
        $inventory_data = $this->generateInventoryMigrationData();
        dispatch(new InventoryDataMigrationJob($this->partner->id, $inventory_data));
    }

    private function generateInventoryMigrationData()
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
}