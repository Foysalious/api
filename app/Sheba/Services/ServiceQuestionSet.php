<?php namespace Sheba\Services;


class ServiceQuestionSet
{
    private $services;

    /**
     * @param $services
     * @return ServiceQuestionSet
     */
    public function setServices($services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getServiceQuestionSet()
    {
        return $this->serviceQuestionSet($this->services);
    }

    /**
     * @param $services
     * @return mixed
     */
    private function serviceQuestionSet($services)
    {
        foreach ($services as &$service) {
            $questions = null;
            $service['type'] = 'normal';
            if ($service->variable_type == 'Options') {
                $questions = json_decode($service->variables)->options;
                $option_contents = $service->options_content ? json_decode($service->options_content, true) : [];
                foreach ($questions as $option_keys => &$question) {
                    $question = collect($question);
                    $question->put('input_type', $this->resolveInputTypeField($question->get('answers')));
                    $question->put('screen', count($questions) > 3 ? 'slide' : 'normal');
                    $option_key = $option_keys + 1;
                    $option_content = key_exists($option_key, $option_contents) ? $option_contents[$option_key] : [];
                    $explode_answers = explode(',', $question->get('answers'));
                    $contents = [];
                    $answer_contents = [];
                    foreach ($explode_answers as $answer_keys => $answer) {
                        $answer_key = $answer_keys + 1;
                        $value = key_exists($answer_key, $option_content) ? $option_content[$answer_key] : null;
                        array_push($contents, $value);
                        array_push($answer_contents, ['key' => $answer_keys, 'content' => $value]);
                    }
                    $question->put('answers', $explode_answers);
                    $question->put('contents', $contents);
                    $question->put('answer_contents', $answer_contents);
                }
                if (count($questions) == 1) {
                    $questions[0]->put('input_type', 'selectbox');
                }
            }
            $service['questions'] = $questions;
            $service['faqs'] = json_decode($service->faqs);
            $service['features'] = json_decode($service->features);
            $service['terms_and_conditions'] = json_decode($service->terms_and_conditions);
            array_forget($service, 'variables');
            array_forget($service, 'options_content');
        }
        return $services;
    }

    /**
     * @param $answers
     * @return string
     */
    private function resolveInputTypeField($answers)
    {
        $answers = explode(',', $answers);
        return count($answers) <= 4 ? "radiobox" : "dropdown";
    }
}