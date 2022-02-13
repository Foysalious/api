<?php namespace Sheba\Service;

use Sheba\Dal\Service\Service;

class SearchableComboService
{
    /** @var Service */
    private $service;

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    public function toSearchableArray()
    {
        $combo_services = [];
        foreach ($this->service->comboServices as $comboService) {
            $service_question = new ServiceQuestion();
            $questions = $this->service->isOptions() ? $service_question->setService($this->service)->getQuestionForThisOption(json_decode($comboService->option)) : [];
            array_push($combo_services, [
                'questions' => $questions,
                'quantity' => (double)$comboService->quantity,
                'option' => json_decode($comboService->option)
            ]);
        }
        return $combo_services;
    }
}