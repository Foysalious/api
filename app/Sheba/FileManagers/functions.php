<?php

if (!function_exists('getEmployeesImagesFolder')) {

    /**
     * Get Employee's Images Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getEmployeesImagesFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/employees_avatar/';
    }
}

if (!function_exists('getUserDefaultAvatar')) {

    /**
     * Get user default avatar file name.
     *
     * @return string
     */
    function getUserDefaultAvatar()
    {
        return getEmployeesImagesFolder(true) . 'default_user.jpg';
    }
}

if (!function_exists('getCategoryBannerFolder')) {

    /**
     * Get Category Banner Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryBannerFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/categories_images/banners/';
    }
}

if (!function_exists('getCategoryIconFolder')) {

    /**
     * Get Category Icon Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryIconFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/categories_images/icons/';
    }
}

if (!function_exists('getCategoryIconPngFolder')) {

    /**
     * Get Category IconPng Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryIconPngFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/categories_images/icons_png/';
    }
}

if (!function_exists('getCategoryGroupBannerFolder')) {

    /**
     * Get Category Group Banner Folder
     *
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryGroupBannerFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/category_groups_images/banners/';
    }
}

if (!function_exists('getCategoryGroupAppBannerFolder')) {

    /**
     * Get Category Group App Banner Folder
     *
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryGroupAppBannerFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/category_groups_images/app_banners/';
    }
}

if(!function_exists('getCategoryGroupThumbFolder')) {
    /**
     * Get Category Group Thumb Folder
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryGroupThumbFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/category_groups_images/thumbs/';
    }
}

if(!function_exists('getCategoryGroupAppThumbFolder')) {
    /**
     * Get Category Group App Thumb Folder
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryGroupAppThumbFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/category_groups_images/app_thumbs/';
    }
}

if(!function_exists('getCategoryGroupIconFolder')) {
    /**
     * Get Category Group App Thumb Folder
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryGroupIconFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/category_groups_images/icons/';
    }
}

if(!function_exists('getCategoryGroupIconPngFolder')) {
    /**
     * Get Category Group App Thumb Folder
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryGroupIconPngFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/category_groups_images/icons_png/';
    }
}

if (!function_exists('getCategoryDefaultBanner')) {

    /**
     * Get Category default Banner file name.
     *
     * @return string
     */
    function getCategoryDefaultBanner()
    {
        return getCategoryBannerFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getCategoryThumbFolder')) {

    /**
     * Get Category Thumb Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryThumbFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/categories_images/thumbs/';
    }
}

if (!function_exists('getCategoryDefaultThumb')) {

    /**
     * Get Category default Thumb file name.
     *
     * @return string
     */
    function getCategoryDefaultThumb()
    {
        return getCategoryThumbFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getServiceBannerFolder')) {

    /**
     * Get Service Banner Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getServiceBannerFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/services_images/banners/';
    }
}

if (!function_exists('getServiceDefaultBanner')) {

    /**
     * Get Service default Banner file name.
     *
     * @return string
     */
    function getServiceDefaultBanner()
    {
        return getServiceBannerFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getServiceThumbFolder')) {

    /**
     * Get Service Thumb Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getServiceThumbFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/services_images/thumbs/';
    }
}

if (!function_exists('getServiceDefaultThumb')) {

    /**
     * Get Service default Thumb file name.
     *
     * @return string
     */
    function getServiceDefaultThumb()
    {
        return getServiceThumbFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getPartnerLogoFolder')) {

    /**
     * Get Partner Logo Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getPartnerLogoFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/partners/logos/';
    }
}

if (!function_exists('getPartnerPackageFolder')) {

    function getPartnerPackageFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');
        return $url . 'images/partners/package/';
    }
}

if (!function_exists('getPartnerDefaultLogo')) {

    /**
     * Get Partner default Logo file name.
     *
     * @return string
     */
    function getPartnerDefaultLogo()
    {
        return getPartnerLogoFolder(true) . 'default.png';
    }
}

if (!function_exists('getPartnerBadgesFolder')) {

    /**
     * Get Partner Badges Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getPartnerBadgesFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/badges/partners/';
    }
}

if (!function_exists('getCustomerBadgesFolder')) {

    /**
     * Get Customer Badges Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getCustomerBadgesFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/badges/customers/';
    }
}

if (!function_exists('getResourceAvatarFolder')) {

    /**
     * Get Resource's Avatar Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getResourceAvatarFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/resources/avatar/';
    }
}

if (!function_exists('getResourceDefaultAvatar')) {

    /**
     * Get resource default avatar file name.
     *
     * @return string
     */
    function getResourceDefaultAvatar()
    {
        return getResourceAvatarFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getResourceNIDFolder')) {

    /**
     * Get Resource's NID Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getResourceNIDFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/resources/nid/';
    }
}

if (!function_exists('getPushNotificationFolder')) {

    function getPushNotificationFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/push_notification/';
    }
}

if (!function_exists('getCustomerAvatarFolder')) {

    /**
     * Get Customer's Avatar Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getCustomerAvatarFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/customer/avatar/';
    }
}

if (!function_exists('getCustomerDefaultAvatar')) {

    /**
     * Get customer default avatar file name.
     *
     * @return string
     */
    function getCustomerDefaultAvatar()
    {
        return getCustomerAvatarFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getSliderImagesFolder')) {

    /**
     * Get Slider Images Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getSliderImagesFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/slides/';
    }
}

if(!function_exists('getFileTypeIcon')) {
    /**
     * Get icon for a file type.
     *
     * @param  $type
     * @return string
     */
    function getFileTypeIcon($type)
    {
        $type = strtolower($type);
        $file_type_icons = [
            'xls' => 'file-excel-o',
            'xlsx' => 'file-excel-o',
            'csv' => 'file-excel-o',
            'ppt' => 'file-powerpoint-o',
            'pptx' => 'file-powerpoint-o',
            'docx' => 'file-word-o',
            'doc' => 'file-word-o',
            'odt' => 'file-word-o',
            'rtf' => 'file-word-o',
            'txt' => 'file-text-o',
            'pdf' => 'file-pdf-o',
            'rar' => 'file-archive-o',
            'zip' => 'file-archive-o',
            'jpg' => 'file-image-o',
            'jpeg' => 'file-image-o',
            'png' => 'file-image-o',
            'gif' => 'file-image-o',
            'mp4' => 'file-video-o',
            'mp3' => 'file-audio-o',
            'wma' => 'file-audio-o'
        ];

        if(!array_key_exists($type, $file_type_icons)) {
            return 'file-o';
        }

        return $file_type_icons[$type];
    }
}

if (!function_exists('getOfferBannerFolder')) {

    /**
     * Get Offer Banner Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getOfferBannerFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/offers_images/banners/';
    }
}

if (!function_exists('getOfferThumbFolder')) {

    /**
     * Get Category Thumb Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getOfferThumbFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/offers_images/thumbs/';
    }
}

if (!function_exists('getBusinessLogoFolder')) {

    /**
     * Get Business Logo Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getBusinessLogoFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/partners/logos/';
    }
}

if (!function_exists('getBusinessDefaultLogo')) {

    /**
     * Get Business default Logo file name.
     *
     * @return string
     */
    function getBusinessDefaultLogo()
    {
        return getBusinessLogoFolder(true) . 'default.png';
    }
}

if (!function_exists('getMemberAvatarFolder')) {

    /**
     * Get Member's Avatar Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getMemberAvatarFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/resources/avatar/';
    }
}

if (!function_exists('getMemberDefaultAvatar')) {

    /**
     * Get resource default avatar file name.
     *
     * @return string
     */
    function getMemberDefaultAvatar()
    {
        return getMemberAvatarFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getMemberNIDFolder')) {

    /**
     * Get Member's NID Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getMemberNIDFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/resources/nid/';
    }
}

if(!function_exists('getNotificationFolder')) {
    /**
     * getNotificationFolder.
     *
     * @return string
     */
    function getNotificationFolder()
    {
        return URL::to('assets/audio/notification');
    }
}

if(!function_exists('getNotificationFileName')) {
    /**
     * getNotificationFileName
     *
     * @param string $sound
     * @return string
     */
    function getNotificationFileName($sound)
    {
        return getNotificationFolder() . '/' . $sound . '.mp3';
    }
}

if(!function_exists('getBase64FileExtension')) {
    /**
     * getBase64FileExtension
     *
     * @param $file
     * @return string
     */
    function getBase64FileExtension($file)
    {
        return image_type_to_extension(getimagesize($file)[2]);
    }
}

if (!function_exists('getProfileDefaultAvatar')) {

    /**
     * Get resource default avatar file name.
     *
     * @return string
     */
    function getProfileDefaultAvatar()
    {
        return getProfileAvatarFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getProfileAvatarFolder')) {

    /**
     * Get Profile's Avatar Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getProfileAvatarFolder($with_base_url = false)
    {
        $url = '';
        if($with_base_url)
            $url = env('S3_URL');

        return $url . 'images/profiles/avatar/';
    }
}

if (!function_exists('getFileName')) {
    function getFileName($file)
    {
        $extension = explode("/", $file);
        return end($extension);
    }
}

if (!function_exists('getFileExtension')) {
    function getFileExtension($file)
    {
        $extension = explode(".", $file);
        return end($extension);
    }
}