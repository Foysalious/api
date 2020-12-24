<?php namespace Sheba\AppSettings\HomePageSetting\DS;

use Carbon\Carbon;
use Sheba\AppSettings\HomePageSetting\Exceptions\UnsupportedTarget;
use Sheba\AppSettings\HomePageSetting\Supported\Targets;

class Item
{
    protected $targetType;
    protected $targetId;
    protected $name;
    protected $icon;
    protected $iconPng;
    protected $appThumb;
    protected $thumb;
    protected $appBanner;
    protected $banner;
    protected $isParent;
    protected $isFlash;
    protected $height;
    /** @var  Carbon */
    protected $validTill;
    protected $voucherCode;
    /** @var  Carbon */
    protected $updatedAt;
    protected $ratio;
    protected $packageName;
    protected $link;
    protected $children;
    protected $slug;

    /**
     * @param string $target_type
     * @return Item
     */
    public function setTargetType($target_type)
    {
        Targets::validate($target_type);
        $this->targetType = $target_type;
        return $this;
    }

    /**
     * @param int $target_id
     * @return Item
     */
    public function setTargetId($target_id)
    {
        $this->targetId = $target_id;
        return $this;
    }

    /**
     * @param mixed $name
     * @return Item
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $icon
     * @return Item
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @param string $icon_png
     * @return Item
     */
    public function setIconPng($icon_png)
    {
        $this->iconPng = $icon_png;
        return $this;
    }

    /**
     * @param string $app_thumb
     * @return Item
     */
    public function setAppThumb($app_thumb)
    {
        $this->appThumb = $app_thumb;
        return $this;
    }

    /**
     * @param string $thumb
     * @return Item
     */
    public function setThumb($thumb)
    {
        $this->thumb = $thumb;
        return $this;
    }

    /**
     * @param string $app_banner
     * @return Item
     */
    public function setAppBanner($app_banner)
    {
        $this->appBanner = $app_banner;
        return $this;
    }

    /**
     * @param string $banner
     * @return Item
     */
    public function setBanner($banner)
    {
        $this->banner = $banner;
        return $this;
    }

    /**
     * @param bool $is_parent
     * @return Item
     */
    public function setIsParent($is_parent)
    {
        $this->isParent = $is_parent;
        return $this;
    }

    /**
     * @param bool $is_flash
     * @return Item
     */
    public function setIsFlash($is_flash)
    {
        $this->isFlash = $is_flash;
        return $this;
    }

    /**
     * @param mixed $height
     * @return Item
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param Carbon $valid_till
     * @return Item
     */
    public function setValidTill(Carbon $valid_till)
    {
        $this->validTill = $valid_till;
        return $this;
    }

    /**
     * @param string $voucher_code
     * @return Item
     */
    public function setVoucherCode($voucher_code)
    {
        $this->voucherCode = $voucher_code;
        return $this;
    }

    /**
     * @param Carbon $updated_at
     * @return Item
     */
    public function setUpdatedAt(Carbon $updated_at)
    {
        $this->updatedAt = $updated_at;
        return $this;
    }

    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
        return $this;
    }

    /**
     * @param mixed $packageName
     * @return Item
     */
    public function setPackageName($packageName)
    {
        $this->packageName = $packageName;
        return $this;
    }

    public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * @param mixed $link
     * @return Item
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function toArray()
    {
        return [
            'target_type' => $this->targetType,
            'target_id' => $this->targetId,
            'name' => $this->name,
            'icon' => $this->icon,
            'icon_png' => $this->iconPng,
            'icon_png_sizes' => getResizedUrls($this->iconPng, 52, 52),
            'app_thumb' => $this->appThumb,
            'app_thumb_sizes' => getResizedUrls($this->appThumb, 100, 100),
            'thumb' => $this->thumb,
            'thumb_sizes' => getResizedUrls($this->thumb, 180, 270),
            'app_banner' => $this->appBanner,
            'app_banner_sizes' => getResizedUrls($this->appBanner, 150, 365),
            'banner' => $this->banner,
            'is_parent' => $this->isParent,
            'is_flash' => $this->isFlash,
            'height' => $this->height,
            'valid_till' => $this->validTill ? $this->validTill->toDateTimeString() : null,
            'voucher_code' => $this->voucherCode,
            'updated_at' => $this->updatedAt ? $this->updatedAt->toDateTimeString() : null,
            'updated_at_timestamp' => $this->updatedAt ? $this->updatedAt->timestamp : null,
            'ratio' => $this->ratio,
            'package_name' => $this->packageName,
            'link' => $this->link,
            'children' => $this->children,
            'slug' => $this->slug
        ];
    }

    /**
     * @param mixed $children
     * @return Item
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @param mixed $slug
     * @return Item
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }
}
