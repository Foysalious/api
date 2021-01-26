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

    public static function AccountDetailsTitle()
    {
        return config('neo_banking.account_details_title');
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

    public static function completionType($complete = 0)
    {
        return $complete ? "success" : "info";
    }

    public static function completionMessage($complete = 0)
    {
        return $complete ? config('neo_banking.completion_success_message') : config('neo_banking.completion_info_message');
    }
}
