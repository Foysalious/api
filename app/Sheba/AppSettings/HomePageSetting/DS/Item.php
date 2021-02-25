<?php namespace Sheba\AppSettings\HomePageSetting\DS;

use Carbon\Carbon;
use Sheba\AppSettings\HomePageSetting\Exceptions\UnsupportedTarget;
use Sheba\AppSettings\HomePageSetting\Supported\Sections;
use Sheba\AppSettings\HomePageSetting\Supported\Targets;

class Item
{
    protected $sectionType;
    protected $targetType;
    protected $targetId;
    protected $name;
    protected $icon;
    protected $iconPng;
    protected $appThumb;
    protected $thumb;
    protected $appBanner;
    protected $banner;
    protected $video;
    protected $isVideo;
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
    protected $startDate;
    protected $endDate;
    protected $variables;
    protected $universalSlug;
    protected $categoryId;

    public function setSectionType($section_type)
    {
        $this->sectionType = $section_type;
        return $this;
    }

    /**
     * @param mixed $variables
     * @return Item
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;
        return $this;
    }

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
     * @param $id
     * @return $this
     */
    public function setTargetId($id)
    {
        $this->targetId = $id;
        return $this;
    }

    /**
     * @param mixed $startDate
     * @return Item
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @param mixed $endDate
     * @return Item
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
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

    /**
     * @param mixed $link
     * @return Item
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
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

    /**
     * @param mixed $video_info
     * @return Item
     */
    public function setVideo($video_info)
    {
        $this->video = json_decode($video_info);
        return $this;
    }

    /**
     * @param $is_video
     * @return Item
     */
    public function setIsVideo($is_video)
    {
        $this->isVideo = $is_video;
        return $this;
    }

    public function setUniversalSlug($universal_slug)
    {
        $this->universalSlug = $universal_slug;
        return $this;
    }

    /**
     * @param $categoryId
     * @return Item
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function toArray()
    {
        return [
                'target_type' => $this->targetType,
                'target_id' => $this->targetId ? (int)$this->targetId : null,
                'category_id' => $this->categoryId ? (int)$this->categoryId : null,
                'name' => $this->name,
                'icon' => $this->icon,
                'icon_png' => $this->iconPng,
                'app_thumb' => $this->appThumb,
                'thumb' => $this->thumb,
                'app_banner' => $this->appBanner,
                'banner' => $this->banner,
                'video' => $this->video,
                'is_parent' => $this->isParent,
                'is_flash' => $this->isFlash,
                'is_video' => $this->isVideo,
                'height' => $this->height,
                'valid_till' => $this->validTill ? $this->validTill->toDateTimeString() : null,
                'voucher_code' => $this->voucherCode,
                'updated_at' => $this->updatedAt ? $this->updatedAt->toDateTimeString() : null,
                'updated_at_timestamp' => $this->updatedAt ? $this->updatedAt->timestamp : null,
                'ratio' => $this->ratio,
                'package_name' => $this->packageName,
                'link' => $this->link,
                'children' => $this->children,
                'slug' => $this->slug,
                'universal_slug' => $this->universalSlug,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'variables' => $this->variables
            ] + $this->getResizedImages();
    }

    private function getResizedImages()
    {
        // Category images are considered as default. Reason: NFI.
        $category_sizes = config('image_sizes.category');
        $icon_png_size = $category_sizes['icon_png'];
        $thumb_size = $category_sizes['thumb'];
        $app_thumb_size = $category_sizes['app_thumb'];
        $app_banner_size = $category_sizes['app_banner'];

        if ($this->sectionType == Sections::SLIDER) {
            $app_banner_size = config('image_sizes.slider.app_banner');
        } else if ($this->sectionType == Sections::BANNER || $this->sectionType == Sections::BANNER_GROUP) {
            $app_banner_size = config('image_sizes.offer.app_banner');
        }

        return [
            'icon_png_sizes' => getResizedUrls($this->iconPng, $icon_png_size['height'], $icon_png_size['width']),
            'app_thumb_sizes' => getResizedUrls($this->appThumb, $app_thumb_size['height'], $app_thumb_size['width']),
            'thumb_sizes' => getResizedUrls($this->thumb, $thumb_size['height'], $thumb_size['width']),
            'app_banner_sizes' => getResizedUrls($this->appBanner, $app_banner_size['height'], $app_banner_size['width']),
        ];
    }
}
