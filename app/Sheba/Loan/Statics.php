<?php


namespace Sheba\Loan;


use Sheba\Dal\PartnerBankLoan\LoanTypes;

class Statics
{
    const BIG_BANNER              = 'images/offers_images/banners/loan_banner_v5_1440_628.jpg';
    const BANNER                  = 'images/offers_images/banners/loan_banner_v5_720_324.jpg';
    const RUNNING_MICRO_LOAN_ICON = "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner/loans/running_robi_topup.png";
    const RUNNING_TERM_LOAN_ICON  = "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner/loans/running_term_loan.png";

    public static function loanList()
    {
        return [
            'loan_list' => [
                [
                    'title'     => 'Micro Loan',
                    'title_bn'  => 'রবি টপআপ লোন',
                    'loan_type' => LoanTypes::MICRO,
                    'loan_icon' => "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner/loans/robi_topup.png"
                ],
                [
                    'title'     => 'Term Loan',
                    'title_bn'  => 'টার্ম লোন',
                    'loan_type' => LoanTypes::TERM,
                    'loan_icon' => "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner/loans/term_loan.png"
                ]
            ]
        ];
    }

    public static function bigBanner()
    {
        return config('sheba.s3_url') . self::BIG_BANNER;
    }

    static function banner()
    {
        return config('sheba.s3_url') . self::BANNER;
    }

    public static function webViews()
    {
        return [
            'digital_loan' => (config('sheba.partners_url') . "/api/digital-loan"),
            'micro_loan'   => (config('sheba.partners_url') . "/api/micro-loan"),
            'term_loan'    => (config('sheba.partners_url') . "/api/term-loan")
        ];
    }

    public static function homepage()
    {
        return [
            [
                'title'     => 'ব্যাংক লোনের সুবিধা কি কি - ',
                'list'      => [
                    'সহজ শর্তে লোন নিন',
                    'জামানত বিহীন লোন নিন',
                    'ঘরে বসেই লোনের আবেদন করুন',
                    'ঘরে বসেই লোন পরিশোধ করুন'
                ],
                'list_icon' => 'icon'
            ],
            [
                'title'     => 'ব্যাংক লোন কিভাবে নেবেন- ',
                'list'      => [
                    'sManager অ্যাপ থেকে প্রয়োজনীয় সকল তথ্য পুরন করুন',
                    'লোন ক্যলকুলেটর দিয়ে হিসাব করে কিস্তির ধারনা নিন',
                    'লোনের আবেদন নিশ্চিত করুন',
                    'সেবা ও ব্যঙ্ক থেকে যাচাই করার পরে খুব দ্রুত আপনার কাছে লোন পৌঁছে যাবে'
                ],
                'list_icon' => 'number'
            ]
        ];
    }
}
