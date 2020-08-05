<?php namespace Sheba\Payment\Presenter;

use Sheba\PresentableDTO;

class PaymentMethodDetails extends PresentableDTO
{
    private $name;
    private $isPublished = true;
    private $description = "";
    private $asset;
    private $methodName;

    public function __construct($method_name)
    {
        $details = (include dirname(__FILE__) . "/method_details.php")[$method_name];
        $this->name = $details['name'];
        $this->asset = $details['asset'];
        $this->methodName = $details['method_name'];
    }

    /**
     * @param bool $is_published
     * @return $this
     */
    public function setIsPublished($is_published)
    {
        $this->isPublished = $is_published;
        return $this;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'is_published' => (int)$this->isPublished,
            'description' => $this->description,
            'asset' => $this->asset,
            'method_name' => $this->methodName
        ];
    }
}
