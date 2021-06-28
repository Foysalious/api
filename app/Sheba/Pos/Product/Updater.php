<?php namespace Sheba\Pos\Product;

use App\Models\PartnerPosService;
use App\Repositories\FileRepository;
use App\Sheba\Pos\Product\Accounting\ExpenseEntry;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\Dal\PartnerPosServiceImageGallery\Model as PartnerPosServiceImageGallery;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Pos\Repositories\Interfaces\PosServiceLogRepositoryInterface;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Subscription\Partner\Access\AccessManager;

class Updater
{
    use FileManager, CdnFileManager, ModificationFields;

    private $data;
    private $updatedData;
    /** @var PosServiceRepositoryInterface */
    private $serviceRepo;
    private $service;
    private $posServiceLogRepo;
    /**
     * @var ExpenseEntry
     */
    private $stockExpenseEntry;

    /**
     * Updater constructor.
     * @param PosServiceRepositoryInterface $service_repo
     * @param PosServiceLogRepositoryInterface $pos_service_log_repo
     */
    public function __construct(PosServiceRepositoryInterface $service_repo, PosServiceLogRepositoryInterface $pos_service_log_repo, ExpenseEntry $stockExpenseEntry)
    {
        $this->serviceRepo = $service_repo;
        $this->posServiceLogRepo = $pos_service_log_repo;
        $this->stockExpenseEntry = $stockExpenseEntry;
    }

    public function setService(PartnerPosService $service)
    {
        $this->service = $service;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function update()
    {
        $this->saveImages();
        $this->format();
        $image_gallery = [];
        if (isset($this->updatedData['image_gallery']))
            $image_gallery = json_decode($this->updatedData['image_gallery'], true);
        $cloned_data = $this->data;
        $this->data = array_except($this->data, ['remember_token', 'discount_amount', 'end_date', 'manager_resource', 'partner', 'category_id', 'is_vat_percentage_off', 'is_stock_off', 'image_gallery','accounting_info']);
        if (!empty($this->updatedData)) $this->updatedData = array_except($this->updatedData, 'image_gallery');
        if (!empty($this->updatedData)) {
            $old_service = clone $this->service;
            $this->serviceRepo->update($this->service, $this->updatedData);
            $this->storeLogs($old_service, $this->updatedData);
        }
        $this->storeImageGallery($image_gallery);
        if(isset($cloned_data['accounting_info']) && !empty($cloned_data['accounting_info']))
            $this->createExpenseEntry($this->service,$cloned_data);

    }

    private function createExpenseEntry($partner_pos_service,$data)
    {
        $accounting_info = json_decode($data['accounting_info'],true);
        $this->stockExpenseEntry->setPartner($partner_pos_service->partner)->setName($partner_pos_service->name)->setId($partner_pos_service->id)->setNewStock($accounting_info['new_stock'])->setCostPerUnit($partner_pos_service->cost)->setAccountingInfo($accounting_info)->create();
    }

    private function storeImageGallery($image_gallery)
    {

        $data = [];
        collect($image_gallery)->each(function ($image) use (&$data) {
            array_push($data, [
                    'partner_pos_service_id' => $this->service->id,
                    'image_link' => $image
                ] + $this->modificationFields(true, false));
        });
        return PartnerPosServiceImageGallery::insert($data);
    }

    private function saveImages()
    {
        if ($this->hasFile('app_thumb')) $this->updatedData['app_thumb'] = $this->saveAppThumbImage();
        else if (array_key_exists('app_thumb', $this->data) && (is_null($this->data['app_thumb']) || $this->data['app_thumb'] == "null")) $this->updatedData['app_thumb'] = config('sheba.s3_url') . 'images/pos/services/thumbs/default.jpg';
        if (isset($this->data['image_gallery']) || isset($this->data['deleted_image'])) $this->updatedData['image_gallery'] = $this->updateImageGallery();
    }

    /**
     * @return false|string
     */
    private function updateImageGallery()
    {
        $image_gallery = [];
        if (isset($this->data['image_gallery'])) {
            foreach ($this->data['image_gallery'] as $key => $file) {
                list($file, $filename) = $this->makeImageGallery($file, '_' . getFileName($file) . '_product_image');
                $image_gallery[] = $this->saveFileToCDN($file, getPosServiceImageGalleryFolder(), $filename);;
            }
        }

        if (isset($this->data['deleted_image'])) {
            $this->data['deleted_image_link'] = PartnerPosServiceImageGallery::whereIn('id', $this->data['deleted_image'])->pluck('image_link')->toArray();
            $this->deleteFromCDN($this->data['deleted_image_link']);
            $this->deleteFromDB($this->data['deleted_image']);
        }
        return !empty($image_gallery) ? json_encode($image_gallery) : null;
    }

    private function deleteFromCDN($files)
    {
        foreach ($files as $file) {
            $filename = substr($file, strlen(env('S3_URL')));
            if (!preg_match('/default/', $filename)) {
                (new FileRepository())->deleteFileFromCDN($filename);
            }
        }
    }

    private function deleteFromDB($deleted_image)
    {
        if (($deleted_image = PartnerPosServiceImageGallery::whereIn('id', $deleted_image)))
            $deleted_image->delete();
    }

    private function hasFile($filename)
    {
        return array_key_exists($filename, $this->data) && ($this->data[$filename] instanceof Image || ($this->data[$filename] instanceof UploadedFile && $this->data[$filename]->getPath() != ''));
    }

    /**
     * Save profile image for resource
     *
     * @return string
     */
    private function saveAppThumbImage()
    {
        $name = isset($this->data['name']) ? $this->data['name'] : $this->service->name;
        list($avatar, $avatar_filename) = $this->makePosServiceAppThumb($this->data['app_thumb'], $name);
        return $this->saveImageToCDN($avatar, getPosServiceThumbFolder(), $avatar_filename);
    }

    private function format()
    {
        if ((isset($this->data['is_stock_off']) && ($this->data['is_stock_off'] == 'true' && $this->service->getStock() != null))) {
            $this->updatedData['stock'] = null;
        }

        if (isset($this->data['is_stock_off']) && $this->data['is_stock_off'] == 'false') {
            $this->updatedData['stock'] = (double)$this->data['stock'];
        }

        if ((isset($this->data['is_vat_percentage_off']) && $this->data['is_vat_percentage_off'] == 'true')) {
            $this->updatedData['vat_percentage'] = null;
        } else if (isset($this->data['vat_percentage']) && $this->data['vat_percentage'] != $this->service->vat_percentage) {
            $this->updatedData['vat_percentage'] = (double)$this->data['vat_percentage'];
        }

        if ((isset($this->data['is_warranty_off']) && $this->data['is_warranty_off'] == 'true')) {
            $this->updatedData['warranty'] = null;
        } else if (isset($this->data['warranty']) && $this->data['warranty'] != $this->service->warranty) {
            $this->updatedData['warranty'] = $this->data['warranty'];
        }

        if ((isset($this->data['weight']) && $this->data['weight'] != $this->service->weight)) {
            $this->updatedData['weight'] = $this->data['weight'];
        }

        if ((isset($this->data['weight_unit']) && $this->data['weight_unit'] != $this->service->weight_unit)) {
            $this->updatedData['weight_unit'] = $this->data['weight_unit'];
        }

        if (isset($this->data['warranty_unit']) && $this->data['warranty_unit'] == "null") {
            $this->updatedData['warranty_unit'] = config('pos.warranty_unit.day.en');
        } else if (isset($this->data['warranty_unit']) && $this->data['warranty_unit'] != $this->service->warranty_unit) {
            $this->updatedData['warranty_unit'] = $this->data['warranty_unit'];
        }

        if ((isset($this->data['is_unit_off']) && $this->data['is_unit_off'] == 'true')) {
            $this->updatedData['unit'] = null;
        } else if (isset($this->data['unit']) && $this->data['unit'] != $this->service->unit) {
            $this->updatedData['unit'] = $this->data['unit'];
        }
        if ((isset($this->data['pos_category_id']) && $this->data['pos_category_id'] != $this->service->pos_category_id)) {
            $this->updatedData['pos_category_id'] = $this->data['pos_category_id'];
        }
        if ((isset($this->data['cost']) && $this->data['cost'] != $this->service->cost)) {
            $this->updatedData['cost'] = $this->data['cost'];
        }
        if ((isset($this->data['price']) && $this->data['price'] != $this->service->price)) {
            $this->updatedData['price'] = $this->data['price'] ?: null;
        }
        if ((isset($this->data['wholesale_price']) && $this->data['wholesale_price'] != $this->service->wholesale_price)) {
            $this->updatedData['wholesale_price'] = $this->data['wholesale_price'];
        }
        if ((isset($this->data['name']) && $this->data['name'] != $this->service->name)) {
            $this->updatedData['name'] = $this->data['name'];
        }
        if ((isset($this->data['category_id']) && $this->data['category_id'] != $this->service->pos_category_id)) {
            $this->updatedData['pos_category_id'] = $this->data['category_id'];
        }
        if ((isset($this->data['unit']) && $this->data['unit'] != $this->service->unit)) {
            $this->updatedData['unit'] = $this->data['unit'];
        }
        if ((isset($this->data['description']) && $this->data['description'] != $this->service->description)) {
            $this->updatedData['description'] = $this->data['description'];
        }
        if ((isset($this->data['show_image']) && $this->data['show_image'] != $this->service->show_image)) {
            $this->updatedData['show_image'] = $this->data['show_image'];
        }
        if ((isset($this->data['shape']) && $this->data['shape'] != $this->service->shape)) {
            $this->updatedData['shape'] = $this->data['shape'];
        }
        if ((isset($this->data['color']) && $this->data['color'] != $this->service->color)) {
            $this->updatedData['color'] = $this->data['color'];
        }
        if ((isset($this->data['is_published_for_shop']) && $this->data['is_published_for_shop'] != $this->service->is_published_for_shop)) {
            if ($this->data['is_published_for_shop'] == 1) {
                if (PartnerPosService::webstorePublishedServiceByPartner($this->service->partner->id)->count() >= config('pos.maximum_publishable_product_in_webstore_for_free_packages'))
                    AccessManager::checkAccess(AccessManager::Rules()->POS->ECOM->PRODUCT_PUBLISH, $this->service->partner->subscription->getAccessRules());
                $this->updatedData['is_published_for_shop'] = $this->data['is_published_for_shop'];
            } else {
                $this->updatedData['is_published_for_shop'] = $this->data['is_published_for_shop'];
            }
        }

    }

    /**
     * @param PartnerPosService $service
     * @param $updated_data
     */
    public function storeLogs(PartnerPosService $service, $updated_data)
    {
        $field_names = [];
        $old_value = [];
        $new_value = [];
        $service = $service->toArray();
        foreach ($updated_data as $field_name => $value) {
            $field_names[] = $field_name;
            $old_value[$field_name] = $service[$field_name];
            $new_value[$field_name] = $value;
        }

        $data = [
            'partner_pos_service_id' => $service['id'],
            'field_names' => json_encode($field_names),
            'old_value' => json_encode($old_value),
            'new_value' => json_encode($new_value)
        ];
        $this->posServiceLogRepo->create($data);
    }
}
