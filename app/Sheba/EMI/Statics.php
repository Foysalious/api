<?php

namespace Sheba\EMI;

class Statics
{
    public static function getMinimumEmiAmount()
    {
        return config('emi.manager.minimum_emi_amount');
    }

    public static function getEmiFaqWebView(): string
    {
        return config('sheba.partners_url') . "/emi/details";
    }

    public static function emiFaq(): array
    {
        return [
            [
                'tag'          => 'emi_benefits',
                'header_label' => 'কিস্তি (EMI) এর সুবিধা কি কি-',
                'data' => [
                    '১. ৫ হাজার টাকার অধিক মূল্যের পণ্য কিস্তিতে বিক্রি করতে পারবেন। যা আপনার বিক্রি বাড়াবে।',
                    '২. কিস্তির বকেয়া টাকা আপনাকে বহন করতে হবে না, ব্যাংক বহন করবে।',
                    '৩. POS মেশিন ছাড়াই ক্রেডিট কার্ড এর মাধ্যমে EMI তে বিক্রি করতে পারবেন ।'
                ]
            ],
            [
                'tag'          => 'how_to_emi',
                'header_label' => 'কিস্তি (EMI) সুবিধা কিভাবে দিবেন-',
                'data'   => [
                    '১. POS. থেকে পণ্য সিলেক্ট করুন অথবা৷ EMI থেকে বিক্রির সমমূল্যের টাকা নির্ধারন করুন।',
                    '২. EMI এর লিংক কাস্টমার এর সাথে শেয়ার করুন।',
                    '৩. কাস্টমার প্রেমেন্ট নিশ্চিত করলে আপনার সেবা ক্রেডিট এ টাকা চেক করে পণ্য বুঝিয়ে দিন।'
                ]
            ]
        ];
    }

    public static function homeData(): array
    {
        return [
            'minimum_amount' => self::getMinimumEmiAmount(),
            'emi_home'       => self::emiFaq()
        ];
    }

    public static function homeV3Data(): array
    {
        return [
            'data' => [
                'minimum_amount' => self::getMinimumEmiAmount(),
                'emi_faq_web_view' => self::getEmiFaqWebView()
            ]
        ];
    }
}