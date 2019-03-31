<?php namespace Sheba\Reports;

abstract class Presenter
{
    /** @return array */
    abstract public function get();

    /** @return array */
    abstract public function getForView();
}