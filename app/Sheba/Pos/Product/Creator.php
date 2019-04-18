<?php namespace App\Sheba\Pos\Product;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Repositories\PosServiceDiscountRepository;
use Sheba\Repositories\PosServiceRepository;

class Creator
{
    use FileManager, CdnFileManager;

    private $data;
    private $serviceRepo;
    private $discountRepo;

    public function __construct(PosServiceRepository $service_repo, PosServiceDiscountRepository $discount_repo)
    {
        $this->serviceRepo = $service_repo;
        $this->discountRepo = $discount_repo;
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
        $this->data['price'] = (double)$this->data['price'];
        $this->format();
        $this->data = array_except($this->data, ['remember_token', 'discount_amount', 'end_date', 'manager_resource', 'partner', 'category_id']);

        return $this->serviceRepo->save($this->data);
    }

    private function saveImages()
    {
        if ($this->hasFile('app_thumb')) $this->data['app_thumb'] = $this->saveAppThumbImage();
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

    private function format()
    {
        $this->data['stock'] = (isset($this->data['stock']) && $this->data['stock'] > 0) ? (double)$this->data['stock'] : null;
        $this->data['vat_percentage'] = (isset($this->data['vat_percentage']) && $this->data['vat_percentage'] > 0) ? (double)$this->data['vat_percentage'] : 0.00;
    }

    private function hasFile($filename)
    {
        return array_key_exists($filename, $this->data) && ($this->data[$filename] instanceof Image || ($this->data[$filename] instanceof UploadedFile && $this->data[$filename]->getPath() != ''));
    }
}