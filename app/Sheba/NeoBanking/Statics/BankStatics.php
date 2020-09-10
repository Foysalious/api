<?php


namespace Sheba\NeoBanking\Statics;


use Sheba\NeoBanking\Exceptions\InvalidBankCode;

class BankStatics
{
    public static function classMap()
    {
        return [
            'NEO_1' => 'PrimeBank',
        ];
    }

    public static function AccountDetailsURL()
    {
        return config('neo_banking.account_details_url');
    }

    /**
     * @param $bankCode
     * @return mixed
     * @throws InvalidBankCode
     */
    public static function BankCategoryList($bankCode)
    {
        $categoryList = config('neo_banking.category_list');
        if (isset($categoryList[$bankCode])) return $categoryList[$bankCode];
        throw  new InvalidBankCode();
    }

    public static function categoryTitles($code)
    {
        $titles = config('neo_banking.category_titles');
        if (isset($titles[$code])) return $titles[$code];
        return ['en' => '', 'bn' => ''];
    }
}
