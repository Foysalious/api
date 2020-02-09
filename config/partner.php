<?php

return [
    'subscription_featured_package_id' => explode(',', env('PARTNER_SUBSCRIPTION_FEATURED_PACKAGE_ID', 3)),
    'subscription_billing_type' => [
        'monthly' => 'monthly',
        'half_yearly' => 'half_yearly',
        'yearly' => 'yearly'
    ],
    'referral_steps' => [
         [
            'step' => '১ম',
            'amount' => '১০০',
            'duration' =>  '৬',
            'nid_verification' => false,
            'details' => 'আপনার রেফার করা বন্ধুকে sManager অ্যাপ ৬ দিন ব্যাবহার করতে হবে।'
        ],
        [
            'step' => '২য়',
            'amount' => '১০০',
            'duration' =>  '১২',
            'nid_verification' => false,
            'details' => 'আপনার রেফার করা বন্ধুকে sManager অ্যাপ ১২ দিন ব্যাবহার করতে হবে।'
        ],
        [
            'step' => '৩য়',
            'amount' => '১০০',
            'duration' =>  '২৫',
            'nid_verification' => false,
            'details' => 'আপনার রেফার করা বন্ধুকে sManager অ্যাপ ২৫ দিন ব্যাবহার করতে হবে।'
        ],
        [
            'step' => '৪র্থ',
            'amount' => '১০০',
            'duration' =>  '৫০',
            'nid_verification' => false,
            'details' => 'আপনার রেফার করা বন্ধুকে sManager অ্যাপ ৫০ দিন ব্যাবহার করতে হবে।'
        ],
        [
            'step' => '৫ম',
            'amount' => '১০০',
            'duration' =>  '',
            'nid_verification' => true,
            'details' => 'আপনার বন্ধুকে sManager অ্যাপের মাধ্যমে NID ভেরিফিকেশন করতে হবে।'
        ],
    ],

    'qr_code' => [
        'account_types' => [
            'bkash' => ['key' => 'BKASH', 'en' => 'bkash', 'bn' => 'বিকাশ'],
            'rocket' => ['key' => 'ROCKET', 'en' => 'rocket', 'bn' => 'রকেট'],
            'nagad' => ['key' => 'NAGAD', 'en' => 'nagad', 'bn' => 'নগদ'],
            'mastercard' => ['key' => 'MASTERCARD', 'en' => 'mastercard', 'bn' => 'মাস্টারকার্ড'],


        ],
        'description' => 'ঘরে-বাইরে যেকোনো সময় দ্রুত পেমেন্ট সংগ্রহ করার জন্য আপনার বিকাশ, নগদ, মাস্টার কার্ড অথবা অন্যান্য একাউন্টের QR কোডের ছবি আপলোড করুন।',
        'slider_image' => [
            'https://cdn-shebadev.s3.ap-south-1.amazonaws.com/partner_assets/assets/images/home_v3/qr_banner_01.jpg',
            'https://cdn-shebadev.s3.ap-south-1.amazonaws.com/partner_assets/assets/images/home_v3/qr_banner_02.jpg'
        ]
    ]
];