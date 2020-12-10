<?php namespace Sheba\Pos\Product;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Pos\Repositories\PosServiceRepository;
use Sheba\RequestIdentification;

class Creator
{
    use FileManager, CdnFileManager;

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
        $this->format();
        $this->data = array_except($this->data, ['remember_token', 'discount_amount', 'end_date', 'manager_resource', 'partner', 'category_id']);
        if (isset($this->data['image_gallery'])) $this->data['image_gallery'] = $this->saveImageGallery();
        $partner_pos_service =  $this->serviceRepo->save($this->data + (new RequestIdentification())->get());
        $this->storeImageGallery($partner_pos_service,$this->data['image_gallery']);
        return $partner_pos_service;
    }

    private function saveImages()
    {
        if ($this->hasFile('app_thumb')) $this->data['app_thumb'] = $this->saveAppThumbImage();
    }

    private function storeImageGallery($partner_pos_service,$image_gallery)
    {
        $data = [];
        collect($image_gallery)->each(function($image) use($partner_pos_service, &$data){
            array_push($data, [
                'partner_pos_service_id' => $partner_pos_service->id,
                'image_link' => $image
            ]);
        });
        return $this->imageGalleryRepo->save($data);
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
     * @return false|string
     */
    private function saveImageGallery()
    {
        $image_gallery = [];
        foreach ($this->data['image_gallery'] as $key => $file) {
            if (!empty($file)) {
                list($file, $filename) = $this->makeImageGallery($file, '_' . getFileName($file) . '_product_image');
                $image_gallery[] = $this->saveFileToCDN($file, getPosServiceImageGalleryFolder(), $filename);
            }
        }
        return json_encode($image_gallery);

    }

    private function format()
    {
        $this->data['stock']            = (isset($this->data['stock']) && $this->data['stock'] > 0) ? (double)$this->data['stock'] : null;
        $this->data['vat_percentage']   = (isset($this->data['vat_percentage']) && $this->data['vat_percentage'] > 0) ? (double)$this->data['vat_percentage'] : 0.00;
        $this->data['warranty_unit']    = (isset($this->data['warranty_unit']) && in_array($this->data['warranty_unit'], array_keys(config('pos.warranty_unit')))) ? $this->data['warranty_unit'] : config('pos.warranty_unit.day.en');
        $this->data['wholesale_price']  = (isset($this->data['wholesale_price']) && $this->data['wholesale_price'] > 0) ? (double)$this->data['wholesale_price'] : 0.00;
        $this->data['price']            = (isset($this->data['price']) && $this->data['price'] > 0) ? (double)$this->data['price'] : null;
    }

    private function hasFile($filename)
    {
        return array_key_exists($filename, $this->data) && ($this->data[$filename] instanceof Image || ($this->data[$filename] instanceof UploadedFile && $this->data[$filename]->getPath() != ''));
    }
}
