<?php namespace Sheba\NeoBanking\Banks\Categories;


use Sheba\NeoBanking\DTO\BankFormCategory;

class Nominee extends BankFormCategory
{
    protected $code = 'nominee';

    public function completion()
    {
        return [
            'en' => 75,
            'bn' => '৭৫'
        ];
    }

    public function get()
    {
        // TODO: Implement get() method.
    }

    public function post()
    {
        return $this->last_updated;
    }

    public function getLastUpdated()
    {
        return $this->last_updated;
    }

    public function getDummy()
    {
        // TODO: Implement getDummy() method.
    }
}
