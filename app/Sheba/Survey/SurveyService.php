<?php namespace Sheba\Survey;

use App\Sheba\Survey\SurveyTypes;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethod;
use Sheba\ResellerPayment\Exceptions\InvalidKeyException;

class SurveyService
{
    private $key;

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @throws InvalidKeyException
     */
    public function get()
    {
        $class_map = SurveyTypes::classMap();
        $surveyTypesClassPath = "Sheba\\Survey\\";

        if (isset($class_map[$this->key])) {
            $class = $class_map[$this->key];
            /** @var PaymentMethod $payment_method */
            return app("$surveyTypesClassPath$class");
        }
        throw new InvalidKeyException("Invalid Survey type Key");
    }

}