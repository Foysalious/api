<?php namespace Sheba\MovieTicket\Vendor;


abstract class Vendor
{
    abstract public function init();

    abstract public function generateURIForAction($action);
}