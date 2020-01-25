<?php namespace Sheba\Checkout\Services;

use App\Models\Service;

class ServiceWithPrice
{
    /** @var Service */
    private $service;
    private $id;
    private $cap;
    private $name;
    private $unit;
    private $amount;
    private $option;
    private $discount;
    private $quantity;
    private $minPrice;
    private $questions;
    private $unitPrice;
    private $isPercentage;
    private $originalPrice;
    private $discountedPrice;
    private $shebaContribution;
    private $isMinPriceApplied;
    private $partnerContribution;

    public function __construct(Service $service = null, $data = [])
    {
        if($service) $this->setService($service);
        if(array_key_exists("id", $data)) $this->setId($data["id"]);
        if(array_key_exists("name", $data)) $this->setName($data["name"]);
        if(array_key_exists("unit", $data)) $this->setUnit($data["unit"]);
        if(array_key_exists("cap", $data)) $this->setCap($data["cap"]);
        if(array_key_exists("amount", $data)) $this->setAmount($data["amount"]);
        if(array_key_exists("option", $data)) $this->setOption($data["option"]);
        if(array_key_exists("discount", $data)) $this->setDiscount($data["discount"]);
        if(array_key_exists("quantity", $data)) $this->setQuantity($data["quantity"]);
        if(array_key_exists("min_price", $data)) $this->setMinPrice($data["min_price"]);
        if(array_key_exists("questions", $data)) $this->setQuestions($data["questions"]);
        if(array_key_exists("unit_price", $data)) $this->setUnitPrice($data["unit_price"]);
        if(array_key_exists("is_percentage", $data)) $this->setIsPercentage($data["is_percentage"]);
        if(array_key_exists("original_price", $data)) $this->setOriginalPrice($data["original_price"]);
        if(array_key_exists("discounted_price", $data)) $this->setDiscountedPrice($data["discounted_price"]);
        if(array_key_exists("sheba_contribution", $data)) $this->setShebaContribution($data["sheba_contribution"]);
        if(array_key_exists("is_min_price_applied", $data)) $this->setIsMinPriceApplied($data["is_min_price_applied"]);
        if(array_key_exists("partner_contribution", $data)) $this->setPartnerContribution($data["partner_contribution"]);
    }

    /**
     * @param Service $service
     * @return ServiceWithPrice
     */
    public function setService(Service $service)
    {
        $this->service = $service;
        $this->setId($service->id);
        $this->setName($service->name);
        $this->setUnit($service->unit);
        return $this;
    }

    /**
     * @param mixed $id
     * @return ServiceWithPrice
     */
    public function setId($id)
    {
        $this->id = $id;
        if(!$this->service) $this->setService(Service::find($id));
        return $this;
    }

    /**
     * @param mixed $cap
     * @return ServiceWithPrice
     */
    public function setCap($cap)
    {
        $this->cap = (double)$cap;
        return $this;
    }

    /**
     * @param mixed $name
     * @return ServiceWithPrice
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $unit
     * @return ServiceWithPrice
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return ServiceWithPrice
     */
    public function setAmount($amount)
    {
        $this->amount = (double)$amount;
        return $this;
    }

    /**
     * @param mixed $option
     * @return ServiceWithPrice
     */
    public function setOption($option)
    {
        $this->option = $option;
        if($this->service) {
            $this->setQuestions(json_decode($this->service->getVariableAndOption($option)[1]));
        }
        return $this;
    }

    /**
     * @param mixed $discount
     * @return ServiceWithPrice
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @param mixed $quantity
     * @return ServiceWithPrice
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @param mixed $minPrice
     * @return ServiceWithPrice
     */
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;
        return $this;
    }

    /**
     * @param mixed $questions
     * @return ServiceWithPrice
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;
        return $this;
    }

    /**
     * @param mixed $unitPrice
     * @return ServiceWithPrice
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    /**
     * @param mixed $isPercentage
     * @return ServiceWithPrice
     */
    public function setIsPercentage($isPercentage)
    {
        $this->isPercentage = $isPercentage;
        return $this;
    }

    /**
     * @param mixed $originalPrice
     * @return ServiceWithPrice
     */
    public function setOriginalPrice($originalPrice)
    {
        $this->originalPrice = $originalPrice;
        return $this;
    }

    /**
     * @param mixed $discountedPrice
     * @return ServiceWithPrice
     */
    public function setDiscountedPrice($discountedPrice)
    {
        $this->discountedPrice = $discountedPrice;
        return $this;
    }

    /**
     * @param mixed $shebaContribution
     * @return ServiceWithPrice
     */
    public function setShebaContribution($shebaContribution)
    {
        $this->shebaContribution = (double)$shebaContribution;
        return $this;
    }

    /**
     * @param mixed $isMinPriceApplied
     * @return ServiceWithPrice
     */
    public function setIsMinPriceApplied($isMinPriceApplied)
    {
        $this->isMinPriceApplied = $isMinPriceApplied;
        return $this;
    }

    /**
     * @param mixed $partnerContribution
     * @return ServiceWithPrice
     */
    public function setPartnerContribution($partnerContribution)
    {
        $this->partnerContribution = (double)$partnerContribution;
        return $this;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCap()
    {
        return $this->cap;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return mixed
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * @return mixed
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @return mixed
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @return mixed
     */
    public function getIsPercentage()
    {
        return $this->isPercentage;
    }

    /**
     * @return mixed
     */
    public function getOriginalPrice()
    {
        return $this->originalPrice;
    }

    /**
     * @return mixed
     */
    public function getDiscountedPrice()
    {
        return $this->discountedPrice;
    }

    /**
     * @return mixed
     */
    public function getShebaContribution()
    {
        return $this->shebaContribution;
    }

    /**
     * @return mixed
     */
    public function getIsMinPriceApplied()
    {
        return $this->isMinPriceApplied;
    }

    /**
     * @return mixed
     */
    public function getPartnerContribution()
    {
        return $this->partnerContribution;
    }

    public function toArray()
    {
        return [
            "id" => $this->id,
            "cap" => $this->cap,
            "name" => $this->name,
            "unit" => $this->unit,
            "amount" => $this->amount,
            "option" => $this->option,
            "discount" => $this->discount,
            "quantity" => $this->quantity,
            "min_price" => $this->minPrice,
            "questions" => $this->questions,
            "unit_price" => $this->unitPrice,
            "is_percentage" => $this->isPercentage,
            "original_price" => $this->originalPrice,
            "discounted_price" => $this->discountedPrice,
            "sheba_contribution" => $this->shebaContribution,
            "is_min_price_applied" => $this->isMinPriceApplied,
            "partner_contribution" => $this->partnerContribution
        ];
    }
}
