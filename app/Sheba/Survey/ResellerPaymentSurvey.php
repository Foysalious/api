<?php namespace Sheba\Survey;

use App\Sheba\Survey\Exception\SurveyException;
use Sheba\Dal\Survey\Model as Survey;
use Sheba\Repositories\Interfaces\SurveyInterface;
use Sheba\ResellerPayment\Exceptions\InvalidKeyException;

class ResellerPaymentSurvey implements SurveyInterface
{
    private $partner;

    public function setUser($user): SurveyInterface
    {
        $this->partner = $user;
        return $this;
    }

    public function getQuestions()
    {
        return config('survey.reseller_payment.basic_questions');
    }

    /**
     * @throws InvalidKeyException
     * @throws SurveyException
     */
    public function storeResult($result)
    {
        $this->validateResult($result);
        $survey = Survey::where('user_id',$this->partner->id)
            ->where('key', SurveyKeys::RESELLER_PAYMENT)->where('user_type',get_class($this->partner))->first();
        if($survey)
            throw new SurveyException("Reseller payment survey already exist for this user");
        $data = [
            'user_id' => $this->partner->id,
            'user_type' => get_class($this->partner),
            'key' => 'reseller_payment',
            'result' => $result,
        ];
        Survey::create($data);
    }

    /**
     * @throws InvalidKeyException
     */
    private function validateResult($result)
    {
        $result = json_decode($result);
        foreach ($result as $item) {
            if (!property_exists($item, 'question') || !property_exists($item, 'description')
                || !property_exists($item, 'option') || !property_exists($item, 'answer'))
                throw new InvalidKeyException("Incorrect Result Structure", 422);
        }
    }


}