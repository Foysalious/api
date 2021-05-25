<?php
return [
    'subscription_featured_package_id' => explode(',', env('PARTNER_SUBSCRIPTION_FEATURED_PACKAGE_ID', 3)),
    'subscription_billing_type'        => [
        'monthly'     => 'monthly',
        'half_yearly' => 'half_yearly',
        'yearly'      => 'yearly'
    ],
    'referral_steps'                   => [
        [
            'step'             => '১ম ধাপ',
            'amount'           => 10,
            'duration'         => 6,
            'nid_verification' => false,
            'details'          => 'আপনার রেফার করা বন্ধুকে sManager অ্যাপ ৬ দিন ব্যবহার করতে হবে।',
            'visible'          => true
        ],
        [
            'step'             => '২য় ধাপ',
            'amount'           => 20,
            'duration'         => 12,
            'nid_verification' => true,
            'details'          => 'আপনার রেফার করা বন্ধুকে sManager অ্যাপ ১২ দিন ব্যবহার করতে হবে এবং আপনার ব্যবসায়ী বন্ধুর sManager অ্যাকাউন্টটি অবশ্যই NID ভেরিফাইড হতে হবে।',
            'visible'          => true
        ],
        [
            'step'             => '৩য় ধাপ',
            'amount'           => 30,
            'duration'         => 25,
            'nid_verification' => true,
            'details'          => 'আপনার রেফার করা বন্ধুকে sManager অ্যাপ ২৫ দিন ব্যবহার করতে হবে এবং আপনার ব্যবসায়ী বন্ধুর sManager অ্যাকাউন্টটি অবশ্যই NID ভেরিফাইড হতে হবে।',
            'visible'          => true
        ],
        [
            'step'             => '৪র্থ ধাপ',
            'amount'           => 0,
            'duration'         => 365000,
            'nid_verification' => true,
            'details'          => 'আপনার বন্ধুকে sManager অ্যাপের মাধ্যমে NID ভেরিফিকেশন করতে হবে।',
            'visible'          => true
        ],
    ],
    'referral_base_link'               => 'https://play.google.com/store/apps/details?id=xyz.sheba.managerapp&referrer=utm_source%3D',

    'qr_code'                                => [
        'account_types' => [
            'bkash'      => ['key' => 'BKASH', 'en' => 'bkash', 'bn' => 'বিকাশ'],
            'rocket'     => ['key' => 'ROCKET', 'en' => 'rocket', 'bn' => 'রকেট'],
            'nagad'      => ['key' => 'NAGAD', 'en' => 'nagad', 'bn' => 'নগদ'],
            'mastercard' => ['key' => 'MASTERCARD', 'en' => 'mastercard', 'bn' => 'মাস্টারকার্ড'],


        ],
        'description'   => 'ঘরে-বাইরে যেকোনো সময় দ্রুত পেমেন্ট সংগ্রহ করার জন্য আপনার বিকাশ, নগদ, মাস্টার কার্ড অথবা অন্যান্য একাউন্টের QR কোডের ছবি আপলোড করুন।',
        'slider_image'  => [
            'https://cdn-shebadev.s3.ap-south-1.amazonaws.com/partner_assets/assets/images/home_v3/qr_banner_01.jpg',
            'https://cdn-shebadev.s3.ap-south-1.amazonaws.com/partner_assets/assets/images/home_v3/qr_banner_02.jpg'
        ]
    ],
    'order'                                  => [
        'request_accept_time_limit_in_seconds' => 300,
        'show_resource_list'                   => 0
    ],
    'lowest_version_for_emi_in_home_setting' => 3,
    'procurement_banner'                     => env('DEFAULT_PROCUREMENT_BANNER', 'https://cdn-shebadev.s3.ap-south-1.amazonaws.com/b2b/image/notification/notification-banner.jpg'),
    'webstore_default_banner_id'             => env('WEBSTORE_DEFAULT_BANNER_ID', 8)
];

