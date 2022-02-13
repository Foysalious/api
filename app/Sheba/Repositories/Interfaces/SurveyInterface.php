<?php namespace Sheba\Repositories\Interfaces;

use Sheba\Survey\ResellerPaymentSurvey;

interface SurveyInterface
{
    public function setUser($user): SurveyInterface;

    public function getQuestions();

    public function storeResult($result);
}