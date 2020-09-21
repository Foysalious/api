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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/category_groups_images/app_banners/';
    }
}

if (!function_exists('getCategoryGroupThumbFolder')) {
    /**
     * Get Category Group Thumb Folder
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryGroupThumbFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/category_groups_images/thumbs/';
    }
}

if (!function_exists('getCategoryGroupAppThumbFolder')) {
    /**
     * Get Category Group App Thumb Folder
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryGroupAppThumbFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/category_groups_images/app_thumbs/';
    }
}

if (!function_exists('getCategoryGroupIconFolder')) {
    /**
     * Get Category Group App Thumb Folder
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryGroupIconFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/category_groups_images/icons/';
    }
}

if (!function_exists('getCategoryGroupIconPngFolder')) {
    /**
     * Get Category Group App Thumb Folder
     * @param bool $with_base_url
     * @return string
     */
    function getCategoryGroupIconPngFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/partners/logos/';
    }
}

if (!function_exists('getEmiBankIconsFolder')) {

    /**
     * Get Partner Logo Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getEmiBankIconsFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/emi_bank_icon/';
    }
}

if (!function_exists('getPartnerPackageFolder')) {

    function getPartnerPackageFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');
        return $url . 'images/partners/package/';
    }
}

if (!function_exists('getEmiBankIconsFolder')) {

    /**
     * Get Partner Logo Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getEmiBankIconsFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/emi_bank_icon/';
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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/resources/nid/';
    }
}

if (!function_exists('getNIDFolder')) {

    /**
     * Get Profile's NID Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getNIDFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/profiles/nid/';
    }
}

if (!function_exists('getPushNotificationFolder')) {

    function getPushNotificationFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/slides/';
    }
}

if (!function_exists('getBankStatementImagesFolder')) {

    function getBankStatementImagesFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/profiles/bank_statement_';
    }
}

if (!function_exists('getBankStatementDefaultImage')) {

    /**
     * @return string
     */
    function getBankStatementDefaultImage()
    {
        return getBankStatementImagesFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getTradeLicenceImagesFolder')) {

    function getTradeLicenceImagesFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/profiles/trade_license_attachment_';
    }
}
if (!function_exists('getLoanFolder')) {
    function getLoanFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) {
            $url = env('S3_URL');
        }
        return $url . 'images/profiles/loan_documents/';
    }
}

if (!function_exists('getTradeLicenceDocumentsFolder')) {

    function getTradeLicenceDocumentsFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'partner/trade_license/trade_';
    }
}
if (!function_exists('getLoanDocumentFolder')) {
    function getLoanDocumentsFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'partner/loans/temp';
    }
}

if (!function_exists('getTradeLicenseDefaultImage')) {

    /**
     * @return string
     */
    function getTradeLicenseDefaultImage()
    {
        return getTradeLicenceImagesFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getFileTypeIcon')) {
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
            'xls' => 'file-excel-o', 'xlsx' => 'file-excel-o', 'csv' => 'file-excel-o', 'ppt' => 'file-powerpoint-o', 'pptx' => 'file-powerpoint-o', 'docx' => 'file-word-o', 'doc' => 'file-word-o', 'odt' => 'file-word-o', 'rtf' => 'file-word-o', 'txt' => 'file-text-o', 'pdf' => 'file-pdf-o', 'rar' => 'file-archive-o', 'zip' => 'file-archive-o', 'jpg' => 'file-image-o', 'jpeg' => 'file-image-o', 'png' => 'file-image-o', 'gif' => 'file-image-o', 'mp4' => 'file-video-o', 'mp3' => 'file-audio-o', 'wma' => 'file-audio-o'
        ];

        if (!array_key_exists($type, $file_type_icons)) {
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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

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
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/resources/nid/';
    }
}

if (!function_exists('getNotificationFolder')) {
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

if (!function_exists('getNotificationFileName')) {
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

if (!function_exists('getBase64FileExtension')) {
    /**
     * getBase64FileExtension
     *
     * @param $file
     * @return string
     */
    function getBase64FileExtension($file)
    {
        return image_type_to_extension(getimagesize($file)[2], false);
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
        if ($with_base_url) $url = env('S3_URL');

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

if (!function_exists('getRewardShopDefaultThumb')) {

    /**
     * Get Reward Shop default Thumb file name.
     *
     * @return string
     */
    function getRewardShopDefaultThumb()
    {
        return getRewardShopThumbFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getRewardShopDefaultBanner')) {

    /**
     * Get Reward Shop default Banner file name.
     *
     * @return string
     */
    function getRewardShopDefaultBanner()
    {
        return getRewardShopBannerFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getRewardShopThumbFolder')) {

    /**
     * Get Reward Shop Thumb Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getRewardShopThumbFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = config('s3.url');

        return $url . 'images/reward_product_images/thumbs/';
    }
}

if (!function_exists('getRewardShopBannerFolder')) {

    /**
     * Get Service Thumb Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getRewardShopBannerFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = config('s3.url');

        return $url . 'images/reward_product_images/banners/';
    }
}

if (!function_exists('getBulkTopUpFolder')) {

    /**
     * Get Profile's Avatar Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getBulkTopUpFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'bulk_top_ups/';
    }
}

if (!function_exists('getBulkVendorStoreFolder')) {

    /**
     * Get Profile's Avatar Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getBulkVendorStoreFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'bulk_vendors_store/';
    }
}

if (!function_exists('getPosCategoryDefaultThumb')) {

    /**
     * Get Category default Thumb file name.
     *
     * @return string
     */
    function getPosCategoryDefaultThumb()
    {
        return getPosCategoryThumbFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getPosCategoryThumbFolder')) {

    /**
     * Get Category Thumb Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getPosCategoryThumbFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = config('s3.url');

        return $url . 'images/pos/categories/thumbs/';
    }
}

if (!function_exists('getPosCategoryDefaultBanner')) {

    /**
     * Get Category default Thumb file name.
     *
     * @return string
     */
    function getPosCategoryDefaultBanner()
    {
        return getPosCategoryBannerFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getPosCategoryBannerFolder')) {

    /**
     * Get Category Thumb Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getPosCategoryBannerFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = config('s3.url');

        return $url . 'images/pos/categories/banners/';
    }
}

if (!function_exists('getPosServiceDefaultThumb')) {

    /**
     * Get Service default Thumb file name.
     *
     * @return string
     */
    function getPosServiceDefaultThumb()
    {
        return getPosServiceThumbFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getPosServiceThumbFolder')) {

    /**
     * Get Service Thumb Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getPosServiceThumbFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = config('s3.url');

        return $url . 'images/pos/services/thumbs/';
    }
}

if (!function_exists('getPosServiceDefaultBanner')) {

    /**
     * Get Service default Thumb file name.
     *
     * @return string
     */
    function getPosServiceDefaultBanner()
    {
        return getPosServiceBannerFolder(true) . 'default.jpg';
    }
}

if (!function_exists('getPosServiceBannerFolder')) {

    /**
     * Get Service Thumb Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getPosServiceBannerFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = config('s3.url');

        return $url . 'images/pos/services/banners/';
    }
}

if (!function_exists('getVatRegistrationImagesFolder')) {

    function getVatRegistrationImagesFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'images/partner/vat_registration/vat_';
    }
}

if (!function_exists('getVatRegistrationDocumentsFolder')) {

    function getVatRegistrationDocumentsFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'partner/vat_registration/vat_';
    }
}

if (!function_exists('getDueTrackerAttachmentsFolder')) {
    function getDueTrackerAttachmentsFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'partner/due-list-attachments/';
    }
}

if (!function_exists('getCoWorkerInviteErrorFolder')) {

    /**
     * Get Profile's Avatar Folder.
     *
     * @param bool $with_base_url
     * @return string
     */
    function getCoWorkerInviteErrorFolder($with_base_url = false)
    {
        $url = '';
        if ($with_base_url) $url = env('S3_URL');

        return $url . 'co_worker_invite_error/';
    }

}
if (!function_exists('getPartnerProofOfBusinessFolder')) {
    function getPartnerProofOfBusinessFolder($with_base_url = false, $partner_id=0)
    {
        $url = '';
        if ($with_base_url)
            $url = env('S3_URL');
        return $url . "partner/$partner_id/proof-of-business";
    }
}
