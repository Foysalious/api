<?php namespace Sheba\Pos\Product;

use App\Models\Partner;
use App\Models\PartnerPosService;
use App\Sheba\Pos\Product\Accounting\ExpenseEntry;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;
use Sheba\Dal\PartnerPosServiceBatch\Model as PartnerPosServiceBatch;
use Sheba\Dal\PartnerPosServiceImageGallery\Model as PartnerPosServiceImageGallery;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\RequestIdentification;
use Sheba\Subscription\Partner\Access\AccessManager;

class Creator
{
    use FileManager, CdnFileManager, ModificationFields;

    private $data, $accounting_info;
    private $serviceRepo;
    private $imageGalleryRepo;
    /**
     * @var ExpenseEntry
     */
    private $stockExpenseEntry;

    public function __construct(PosServiceRepositoryInterface $service_repo, PosServiceRepositoryInterface $image_gallery_repo, ExpenseEntry $stockExpenseEntry)
    {
        $this->serviceRepo = $service_repo;
        $this->imageGalleryRepo = $image_gallery_repo;
        $this->stockExpenseEntry =  $stockExpenseEntry;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param mixed $accounting_info
     * @return Creator
     */
    public function setAccountingInfo($accounting_info)
    {
        $this->accounting_info = $accounting_info;
        return $this;
    }

    public function create()
    {
        $this->saveImages();
        $this->data['partner_id'] = $this->data['partner']['id'];
        $this->data['pos_category_id'] = $this->data['category_id'];
        $cost = $this->data['cost'];
        $stock = $this->data['stock'];
        $this->format();
        $image_gallery = null;
        if (isset($this->data['image_gallery'])) $image_gallery = $this->data['image_gallery'];
        $this->data = array_except($this->data, ['remember_token', 'discount_amount', 'end_date', 'manager_resource', 'partner', 'category_id', 'image_gallery','accounting_info']);
        $partner_pos_service = $this->serviceRepo->save($this->data + (new RequestIdentification())->get());
        $this->savePartnerPosServiceBatch($partner_pos_service, $stock, $cost);
        $this->storeImageGallery($partner_pos_service, json_decode($image_gallery,true));
        return $partner_pos_service;
    }

    private function createExpenseEntry($partner_pos_service, $accounting_info)
    {
        $this->stockExpenseEntry->setPartner($partner_pos_service['partner_id'])
            ->setName($partner_pos_service['name'])
            ->setId($partner_pos_service['id'])
            ->setNewStock($partner_pos_service['stock'])
            ->setCostPerUnit($partner_pos_service['cost'])
            ->setAccountingInfo($accounting_info)
            ->create();
    }


    private function saveImages()
    {
        if ($this->hasFile('app_thumb')) $this->data['app_thumb'] = $this->saveAppThumbImage();

        if (isset($this->data['image_gallery'])) $this->data['image_gallery'] = $this->saveImageGallery($this->data['image_gallery']);

    }

    private function storeImageGallery($partner_pos_service,$image_gallery)
    {
        $data = [];
        collect($image_gallery)->each(function($image) use($partner_pos_service, &$data){
            array_push($data, [
                'partner_pos_service_id' => $partner_pos_service->id,
                'image_link' => $image
            ] +  $this->modificationFields(true, false) );
        });
        return PartnerPosServiceImageGallery::insert($data);
    }
    /**
     * Save profile image for resource
     *
     * @return string
     */
    private function saveAppThumbImage()
    {
        list($avatar, $avatar_filename) = $this->makePosServiceAppThumb($this->data['app_thumb'], $this->data['name']);
        return $this->saveImageToCDN($avatar, getPosServiceThumbFolder(), $avatar_filename);
    }

    /**
     * @param $image_gallery
     * @return false|string
     */

    private function saveImageGallery($image_gallery)
    {
        $image_gallery_link = [];
        foreach ($image_gallery as $key => $file) {
            if (!empty($file)) {
                list($file, $filename) = $this->makeImageGallery($file, '_' . getFileName($file) . '_product_image');
                $image_gallery_link[] = $this->saveFileToCDN($file, getPosServiceImageGalleryFolder(), $filename);
            }
        }
        return json_encode($image_gallery_link);

    }

    /**
     * @throws \Sheba\Subscription\Partner\Access\Exceptions\AccessRestrictedExceptionForPackage
     */
    private function format()
    {
        $this->data['vat_percentage']   = (isset($this->data['vat_percentage']) && $this->data['vat_percentage'] > 0) ? (double)$this->data['vat_percentage'] : 0.00;
        $this->data['warranty_unit']    = (isset($this->data['warranty_unit']) && in_array($this->data['warranty_unit'], array_keys(config('pos.warranty_unit')))) ? $this->data['warranty_unit'] : config('pos.warranty_unit.day.en');
        $this->data['wholesale_price']  = (isset($this->data['wholesale_price']) && $this->data['wholesale_price'] > 0) ? (double)$this->data['wholesale_price'] : 0.00;
        $this->data['price']            = (isset($this->data['price']) && $this->data['price'] > 0) ? (double)$this->data['price'] : null;
        $this->data['publication_status']            = isset($this->data['publication_status'])  ?  $this->data['publication_status'] : 1;
        if (isset($this->data['is_published_for_shop']) && $this->data['is_published_for_shop'] == 1) {
            if (PartnerPosService::webstorePublishedServiceByPartner($this->data['partner_id'])->count() >= $this->getPartner($this->data['partner_id'])->subscription->getAccessRules()['pos']['ecom']['product_publish_limit'])
                AccessManager::checkAccess(AccessManager::Rules()->POS->ECOM->PRODUCT_PUBLISH, $this->getPartner($this->data['partner_id'])->subscription->getAccessRules());
        } else {
            $this->data['is_published_for_shop'] = 0;
        }

    }

    private function getPartner($partner_id)
    {
        return Partner::find($this->data['partner_id']);
    }

    private function hasFile($filename)
    {
        return array_key_exists($filename, $this->data) && ($this->data[$filename] instanceof Image || ($this->data[$filename] instanceof UploadedFile && $this->data[$filename]->getPath() != ''));
    }

    public function syncPartnerPosCategory($partner_pos_service)
    {
        $data = [];
        $partner_id = $partner_pos_service->partner_id;
        $master_cat_id = $partner_pos_service->master_category_id;
        $sub_cat_id = $partner_pos_service->sub_category_id;

        $partner_categories = PartnerPosCategory::where('partner_id',$partner_id)->whereIn('category_id',[$master_cat_id,$sub_cat_id])->pluck('category_id')->toArray();

        if(empty($partner_categories) || !in_array($master_cat_id,$partner_categories))
        {
            array_push($data,$this->withCreateModificationField([
                'partner_id' => $partner_id,
                'category_id' => $master_cat_id,
            ]));
        }
        if(empty($partner_categories) || !in_array($sub_cat_id,$partner_categories))
        {
            array_push($data,$this->withCreateModificationField([
                'partner_id' => $partner_id,
                'category_id' => $sub_cat_id,
            ]));
        }

        if(!empty($data))
            PartnerPosCategory::insert($data);
    }

    public function savePartnerPosServiceBatch($service, $stock = null, $cost = null)
    {
        $batchData = [];
        $accounting_data = [];
        $batchData['partner_pos_service_id'] = $service->id;
        $batchData['stock'] = $stock;
        $batchData['cost']  = $cost ?? 0.0;
        if(isset($this->accounting_info)) {
            $accounting_data = (array) (json_decode($this->accounting_info));
            $batchData['from_account'] = $accounting_data['from_account'];
            $batchData['supplier_id'] = $accounting_data['supplier_id'];
        }

        $partner_pos_service_batch = PartnerPosServiceBatch::create($batchData);
        $batchData = $this->makeReturnDataForBatch($partner_pos_service_batch);
        $this->data['stock'] = $batchData['stock'];
        $this->data['cost'] = $batchData['cost'];

        if(isset($this->accounting_info)) $this->createExpenseEntry($service, $accounting_data);
        return $batchData;
    }

    private function makeReturnDataForBatch($partner_pos_service)
    {
        $data = [];
        $data['id'] = $partner_pos_service['partner_pos_service_id'];
        $data['batch_id'] = $partner_pos_service['id'];
        $data['stock'] = $partner_pos_service['stock'];
        $data['cost'] = $partner_pos_service['cost'];
        $data['from_account'] = $partner_pos_service['from_account'];
        $data['supplier_id'] = $partner_pos_service['supplier_id'];
        return $data;
    }
}
