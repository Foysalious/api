<?php

namespace App\Http\Controllers\NeoBanking;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\NeoBanking\NeoBanking;

class NeoBankingController extends Controller
{
    public function __construct()
    {
    }

    public function getOrganizationInformation($partner, Request $request)
    {
        try {
            $bank             = $request->bank;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $info             = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->organizationInformation();
            return api_response($request, $info, 200, ['data' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getHomepage($partner, Request $request)
    {
        try {
            $homepage = [
                [
                    'bank_name' => [
                        'en' => 'Prime Bank',
                        'bn' => 'প্রাইম ব্যাংক'
                    ],
                    'logo' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/images/bank_icon/brac_bank_35_135.png',
                    'has_account' => 1,
                    'account_no' => '2441139',
                    'account_status' =>  'ঠিকানা ভেরিফিকেশন প্রক্রিয়াধিন',
                    'status_message' => 'এই মুহূর্তে আপনার অ্যাকাউন্ট এ শুধু মাত্র টাকা জমা দেয়া যাবে। সম্পুর্ণরুপে অ্যাকাউন্ট সচল করতে আপনার নির্ধারিত শাখায় গিয়ে স্বাক্ষর করুন এবং আপনার ঠিকানা ভেরিফিকেশন এর জন্য অপেক্ষা করুন।',
                    'status_message_type' => 'warning'
                ]
            ];
            return api_response($request, $homepage, 200, ['data' => $homepage]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAccountDetails($partner, Request $request)
    {
        try {
            $bank             = $request->bank;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $account_details             = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->accountDetails();
            return api_response($request, $account_details, 200, ['data' => $account_details]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function createTransaction($partner,Request $request)
    {
        try {
            $this->validate($request,[
                'amount' => 'required|numeric'
            ]);
            $bank             = $request->bank;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $transaction_response           = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->createTransaction();
            return api_response($request, $transaction_response, 200, ['data' => $transaction_response]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

}
