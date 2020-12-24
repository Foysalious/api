<?php


namespace Sheba\Loan\Statics;


use Sheba\Dal\PartnerBankLoan\LoanTypes;

class GeneralStatics
{
    const BIG_BANNER                = 'images/offers_images/banners/loan_banner_v5_1440_628.jpg';
    const BANNER                    = 'images/offers_images/banners/loan_banner_v5_720_324.jpg';
    const RUNNING_MICRO_LOAN_ICON   = "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner/loans/running_robi_topup.png";
    const RUNNING_TERM_LOAN_ICON    = "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner/loans/running_term_loan.png";
    const TERM_TITLE_BD             = "টার্ম লোন";
    const MICRO_TITLE_BD            = "ডানা ক্লাসিক লোন";
    const NOT_ROBI_RETAILER_MESSAGE = "প্রিয় গ্রাহক, আপনি রবি রিটেইলার নন। ডানা ক্লাসিক লোনের জন্য অ্যাপ্লিকেশন করতে আপনাকে অবশ্যই রবি রিটেইলার হতে হবে।";

    public static function validator($version)
    {
        return $version == 2 ? [
            'loan_amount' => 'required|numeric',
            'loan_type'   => 'sometimes|required|in:' . implode(',', LoanTypes::get()),
            'duration'    => 'required_if:loan_type,' . LoanTypes::MICRO . '|integer'
        ] : [
            'loan_amount' => 'required|numeric',
            'duration'    => 'required|integer',
        ];
    }

    public static function loanList()
    {
        return [
            self::termLoanData(),
            self::microLoanData()
        ];
    }

    public static function termLoanData()
    {
        return [
            'title'     => 'Term Loan',
            'title_bn'  => self::TERM_TITLE_BD,
            'loan_type' => LoanTypes::TERM,
            'loan_icon' => "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner/loans/term_loan.png"
        ];
    }

    public static function microLoanData()
    {
        return [
            'title'     => 'Dana Classic Loan',
            'title_bn'  => self::MICRO_TITLE_BD,
            'loan_type' => LoanTypes::MICRO,
            'loan_icon' => "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner/loans/robi_topup.png"
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

    public static function getFee($type)
    {
        $fee = config('loan.fee');
        return $type == LoanTypes::MICRO ? $fee[LoanTypes::MICRO] : $fee[LoanTypes::TERM];
    }

    public static function getMicroLoanAnnualFee()
    {
        return config('loan.micro_loan_annual_fee');
    }

    public static function getClaimTransactionFee()
    {
        return config('loan.micro_loan_claim_transaction_fee');
    }

    public static function getMinimumDay($type)
    {
        $day = config('loan.minimum_day');
        return $type == LoanTypes::MICRO ? $day[LoanTypes::MICRO] : $day[LoanTypes::TERM];
    }

    public static function getMaximumAmount($type)
    {
        $amount = config('loan.maximum_amount');
        return $type == LoanTypes::MICRO ? $amount[LoanTypes::MICRO] : $amount[LoanTypes::TERM];
    }

    public static function getMinimumAmount($type)
    {
        $amount = config('loan.minimum_amount');
        return $type == LoanTypes::MICRO ? $amount[LoanTypes::MICRO] : $amount[LoanTypes::TERM];
    }

    public static function getMinimumRepaymentAmount()
    {
        return config('loan.minimum_repayment_amount');
    }

    public static function getDefaulterFine()
    {
        return config('loan.defaulter_fine');
    }

    public static function getDetailsLink($type)
    {
        return $type == LoanTypes::MICRO ? (config('sheba.partners_url') . "/api/micro-loan") : (config('sheba.partners_url') . "/api/term-loan");
    }

    public static function getUpdateFields()
    {
        return [
            'credit_score',
            'duration',
            'purpose',
            'interest_rate',
            'loan_amount',
            'groups'
        ];
    }

    public static function isValidAmount($type, $amount)
    {
        $min = self::getMinimumAmount($type) ?: 0;
        $max = self::getMaximumAmount($type) ?: 0;
        return $amount >= $min && $amount <= $max;
    }
}
