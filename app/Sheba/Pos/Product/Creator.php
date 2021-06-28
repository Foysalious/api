<?php namespace Sheba\Pos\Product;

use App\Models\Partner;
use App\Models\PartnerPosService;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;
use Sheba\Dal\PartnerPosServiceImageGallery\Model as PartnerPosServiceImageGallery;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Pos\Repositories\PosServiceRepository;
use Sheba\RequestIdentification;
use Sheba\Subscription\Partner\Access\AccessManager;

class Creator
{
    use FileManager, CdnFileManager, ModificationFields;

    private $data;
    private $serviceRepo;
    private $imageGalleryRepo;

    public function __construct(PosServiceRepositoryInterface $service_repo, PosServiceRepositoryInterface $image_gallery_repo)
    {
        $this->serviceRepo = $service_repo;
        $this->imageGalleryRepo = $image_gallery_repo;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function create()
    {
        $this->saveImages();
        $this->data['partner_id'] = $this->data['partner']['id'];
        $this->data['pos_category_id'] = $this->data['category_id'];
        $this->data['cost'] = (double)$this->data['cost'];
        $this->data['name']= json_encode( $this->data['name']);
        $this->format();
        $image_gallery = null;
        if (isset($this->data['image_gallery']))
            $image_gallery = $this->data['image_gallery'];
        $this->data = array_except($this->data, ['remember_token', 'discount_amount', 'end_date', 'manager_resource', 'partner', 'category_id', 'image_gallery']);
        $partner_pos_service = $this->serviceRepo->save($this->data + (new RequestIdentification())->get());
        $this->storeImageGallery($partner_pos_service, json_decode($image_gallery,true));
        return $partner_pos_service;
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
        $this->data['stock']            = (isset($this->data['stock']) && $this->data['stock'] > 0) ? (double)$this->data['stock'] : null;
        $this->data['vat_percentage']   = (isset($this->data['vat_percentage']) && $this->data['vat_percentage'] > 0) ? (double)$this->data['vat_percentage'] : 0.00;
        $this->data['warranty_unit']    = (isset($this->data['warranty_unit']) && in_array($this->data['warranty_unit'], array_keys(config('pos.warranty_unit')))) ? $this->data['warranty_unit'] : config('pos.warranty_unit.day.en');
        $this->data['wholesale_price']  = (isset($this->data['wholesale_price']) && $this->data['wholesale_price'] > 0) ? (double)$this->data['wholesale_price'] : 0.00;
        $this->data['price']            = (isset($this->data['price']) && $this->data['price'] > 0) ? (double)$this->data['price'] : null;
        $this->data['publication_status']            = isset($this->data['publication_status'])  ?  $this->data['publication_status'] : 1;
        if (isset($this->data['is_published_for_shop']) && $this->data['is_published_for_shop'] == 1) {
            if (PartnerPosService::webstorePublishedServiceByPartner($this->data['partner_id'])->count() >= config('pos.maximum_publishable_product_in_webstore_for_free_packages'))
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
}
