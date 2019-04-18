<?php namespace App\Sheba\Pos\Product;

use App\Models\PartnerPosService;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Repositories\PosServiceRepository;

class Updater
{
    use FileManager, CdnFileManager;

    private $data;
    private $updatedData;
    private $serviceRepo;
    private $service;

    public function __construct(PosServiceRepository $service_repo)
    {
        $this->serviceRepo = $service_repo;
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

        if ($this->updatedData) $this->serviceRepo->update($this->service, $this->updatedData);
    }

    private function saveImages()
    {
        if ($this->hasFile('app_thumb')) $this->updatedData['app_thumb'] = $this->saveAppThumbImage();
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
        if ((isset($this->data['is_stock_off']) && $this->data['is_stock_off'])) {
            $this->updatedData['stock'] = null;
        } elseif (isset($this->data['stock']) && $this->data['stock'] != $this->service->stock) {
            $this->updatedData['stock'] = (double)$this->data['stock'];
        }

        if ((isset($this->data['is_vat_percentage_off']) && $this->data['is_vat_percentage_off'])) {
            $this->updatedData['vat_percentage'] = null;
        } else if (isset($this->data['vat_percentage']) && $this->data['vat_percentage'] != $this->service->vat_percentage) {
            $this->updatedData['vat_percentage'] = (double)$this->data['vat_percentage'];
        }

        if ((isset($this->data['pos_category_id']) && $this->data['pos_category_id'] != $this->service->pos_category_id)) {
            $this->updatedData['pos_category_id'] = $this->data['pos_category_id'];
        }

        if ((isset($this->data['cost']) && $this->data['cost'] != $this->service->cost)) {
            $this->updatedData['cost'] = $this->data['cost'];
        }

        if ((isset($this->data['price']) && $this->data['price'] != $this->service->cost)) {
            $this->updatedData['price'] = $this->data['price'];
        }
    }

    private function hasFile($filename)
    {
        return array_key_exists($filename, $this->data) && ($this->data[$filename] instanceof Image || ($this->data[$filename] instanceof UploadedFile && $this->data[$filename]->getPath() != ''));
    }
}