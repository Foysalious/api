<?php namespace Sheba\Service;


use App\Models\Service;

class ServiceQuestion
{
    /** @var Service */
    private $service;

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    public function get()
    {
        if ($this->service->isFixed()) return null;
        $options = (json_decode($this->service->variables))->options;
        $option_contents = $this->service->options_content ? json_decode($this->service->options_content, true) : [];
        foreach ($options as $option_keys => &$option) {
            $option_key = $option_keys + 1;
            $option_content = key_exists($option_key, $option_contents) ? $option_contents[$option_key] : [];
            $option->answers = explode(',', $option->answers);
            $contents = [];
            foreach ($option->answers as $answer_keys => $answer) {
                $answer_key = $answer_keys + 1;
                array_push($contents, key_exists($answer_key, $option_content) ? $option_content[$answer_key] : null);
            }
            $option->contents = $contents;
        }
        return $options;
    }

    public function getQuestionForThisOption(array $option)
    {
        $variables = $this->get();
        $questions = [];
        foreach ($variables as $key => $variable) {
            array_push($questions, [
                'question' => $variable->question,
                'answer' => $variable->answers[$option[$key]],
                'contents' => $variable->contents[$option[$key]]
            ]);
        }
        return $questions;
    }
}