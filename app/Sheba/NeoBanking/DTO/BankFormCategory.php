<?php


namespace Sheba\NeoBanking\DTO;


abstract class BankFormCategory
{
    protected $title;
    protected $data;

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    abstract public function completion();

    abstract public function get();

    abstract public function post();
}
