<?php namespace Sheba\Reports;

abstract class Presenter
{
    /** @var array */
    protected $fields;

    /** @return array */
    abstract public function get();

    /** @return array */
    abstract public function getForView();

    protected function convertToViewKeys()
    {
        $data = $this->get();
        $view_data = [];
        foreach($this->fields as $key => $field) {
            if(array_key_exists($key, $data)) $view_data[$field] = $data[$key];
        }

        return $view_data;
    }
}