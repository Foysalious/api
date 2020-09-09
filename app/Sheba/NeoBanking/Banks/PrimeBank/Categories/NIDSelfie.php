<?php


namespace Sheba\NeoBanking\Banks\PrimeBank\Categories;


use Sheba\NeoBanking\DTO\BankFormCategory;

class NIDSelfie extends BankFormCategory
{
    public function __construct()
    {
        $this->title = [
            'en' => 'NID and Selfie',
            'bn' => 'জাতীয় পরিচয়পত্র ও সেলফি'
        ];
    }

    public function get()
    {
        // TODO: Implement get() method.
    }

    public function completion()
    {
        // TODO: Implement completion() method.
    }

    public function post()
    {
        // TODO: Implement post() method.
    }
}
