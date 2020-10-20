<?php namespace Sheba\NeoBanking\Banks\Categories;


use Sheba\NeoBanking\DTO\BankFormCategory;

class NIDSelfie extends BankFormCategory
{
    protected $code = 'nid_selfie';

    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {
        // TODO: Implement get() method.
    }

    public function completion()
    {
        return [
            'en' => 75,
            'bn' => '৭৫'
        ];
    }

    public function post()
    {
        // TODO: Implement post() method.
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
