<?php namespace Sheba\Repositories\Interfaces;

interface SurveyInterface
{
    public function setUser($user);
    public function getQuestions();
    public function storeResult($result);
}