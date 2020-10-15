<?php


namespace Sheba\NeoBanking\Banks\PrimeBank;
use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use App\Sheba\NeoBanking\Banks\PrimeBank\PrimeBankClient;
use Sheba\NeoBanking\BankApiClient;
use Sheba\NeoBanking\Banks\BankAccountDetailInfo;
use Sheba\NeoBanking\Banks\BankAccountInfo;
use Sheba\NeoBanking\Banks\BankTransaction;

class ApiClient extends BankApiClient
{

    function getAccountInfo(): BankAccountInfo
    {
        return (new BankAccountInfo())->setHasAccount(1)->setAccountNo('2441139')->setAccountStatus('ঠিকানা ভেরিফিকেশন প্রক্রিয়াধিন')->setStatusMessage('এই মুহূর্তে আপনার অ্যাকাউন্ট এ শুধু মাত্র টাকা জমা দেয়া যাবে। সম্পুর্ণরুপে অ্যাকাউন্ট সচল করতে আপনার নির্ধারিত শাখায় গিয়ে স্বাক্ষর করুন এবং আপনার ঠিকানা ভেরিফিকেশন এর জন্য অপেক্ষা করুন।')->setStatusMessageType('warning');
    }

    function getAccountDetailInfo(): BankAccountInfoWithTransaction
    {
        $accountDetailInfo=(new BankAccountDetailInfo([
            'account_name'               => 'AL Amin Rahman',
            'account_no'                 => '2441139',
            'balance'                    => '4000',
            'minimum_transaction_amount' => 1000,
            'transaction_error_msg'      => 'ট্রান্সেকশন সফল হয়েছে'
        ]));
        $transactionList=[];
        foreach ([0,1,2,3,4] as $i){
            $transactionList[]=(new BankTransaction([
                'date'   => '2020-12-01 20:10:33',
                'name'   => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
                'mobile' => '01748712884',
                'amount' => '60000',
                'type'   => 'credit'
            ]));
        }
        return (new BankAccountInfoWithTransaction())->setAccountInfo($accountDetailInfo)->setTransactions($transactionList);
    }

    function getNidInfo($data){
        return (new PrimeBankClient())->post('api/v1/nid-verification',$data);
    }
}
