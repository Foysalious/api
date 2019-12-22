<?php namespace Sheba\Pos\Product;

use App\Models\PartnerPosService;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Pos\Repositories\Interfaces\PosServiceLogRepositoryInterface;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;

class Updater
{
    use FileManager, CdnFileManager;

    private $data;
    private $updatedData;
    /** @var PosServiceRepositoryInterface */
    private $serviceRepo;
    private $service;
    private $posServiceLogRepo;

    /**
     * Updater constructor.
     * @param PosServiceRepositoryInterface $service_repo
     * @param PosServiceLogRepositoryInterface $pos_service_log_repo
     */
    public function __construct(PosServiceRepositoryInterface $service_repo, PosServiceLogRepositoryInterface $pos_service_log_repo)
    {
        $this->serviceRepo       = $service_repo;
        $this->posServiceLogRepo = $pos_service_log_repo;
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
        $this->data = array_except($this->data, ['remember_token', 'discount_amount', 'end_date', 'manager_resource', 'partner', 'category_id', 'is_vat_percentage_off', 'is_stock_off']);

        if (!empty($this->updatedData)) {
            $old_service = clone $this->service;
            $this->serviceRepo->update($this->service, $this->updatedData);
            $this->storeLogs($old_service, $this->updatedData);
        }
    }

    private function saveImages()
    {
        if ($this->hasFile('app_thumb')) $this->updatedData['app_thumb'] = $this->saveAppThumbImage();
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
        if ((isset($this->data['is_stock_off']) && ($this->data['is_stock_off'] == 'true' && $this->service->stock != null))) {
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



    }

    /**
     * @param PartnerPosService $service
     * @param $updated_data
     */
    public function storeLogs(PartnerPosService $service, $updated_data)
    {
        $field_names = [];
        $old_value   = [];
        $new_value   = [];
        $service     = $service->toArray();
        foreach ($updated_data as $field_name => $value) {
            $field_names[]          = $field_name;
            $old_value[$field_name] = $service[$field_name];
            $new_value[$field_name] = $value;
        }

        $data = [
            'partner_pos_service_id' => $service['id'],
            'field_names'            => json_encode($field_names),
            'old_value'              => json_encode($old_value),
            'new_value'              => json_encode($new_value)
        ];
        $this->posServiceLogRepo->create($data);
    }
}
