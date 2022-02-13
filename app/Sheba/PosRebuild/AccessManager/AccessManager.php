<?php namespace App\Sheba\PosRebuild\AccessManager;


use Sheba\Subscription\Partner\Access\AccessManager as PartnerAccessManager;

class AccessManager
{
    private $feature;
    private $accessRules;
    private $productPublishedCount;

    /**
     * @param mixed $feature
     * @return AccessManager
     */
    public function setFeature($feature): AccessManager
    {
        $this->feature = $feature;
        return $this;
    }

    /**
     * @param mixed $accessRules
     * @return AccessManager
     */
    public function setAccessRules($accessRules): AccessManager
    {
        $this->accessRules = $accessRules;
        return $this;
    }

    /**
     * @param mixed $productPublishedCount
     * @return AccessManager
     */
    public function setProductPublishedCount($productPublishedCount): AccessManager
    {
        $this->productPublishedCount = $productPublishedCount;
        return $this;
    }

    /**
     * @return bool
     */
    public function checkAccess(): bool
    {
        if ($this->feature == Features::PRODUCT_WEBSTORE_PUBLISH && $this->productPublishedCount < $this->accessRules['pos']['ecom']['product_publish_limit']) return true;
        try {
            PartnerAccessManager::checkAccess($this->getRules(), $this->accessRules);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function getRules(): string
    {
        if ($this->feature == Features::PRODUCT_WEBSTORE_PUBLISH) return PartnerAccessManager::Rules()->POS->ECOM->PRODUCT_PUBLISH;
        return PartnerAccessManager::Rules()->POS->INVOICE->DOWNLOAD;
    }

}