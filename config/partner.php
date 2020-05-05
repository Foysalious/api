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
            'step'             => '১ম',
            'amount'           => 100,
            'duration'         => 6,
            'nid_verification' => false,
            'details'          => 'আপনার রেফার করা বন্ধুকে sManager অ্যাপ ৬ দিন ব্যাবহার করতে হবে।'
        ],
        [
            'step'             => '২য়',
            'amount'           => 100,
            'duration'         => 12,
            'nid_verification' => false,
            'details'          => 'আপনার রেফার করা বন্ধুকে sManager অ্যাপ ১২ দিন ব্যাবহার করতে হবে।'
        ],
        [
            'step'             => '৩য়',
            'amount'           => 100,
            'duration'         => 25,
            'nid_verification' => false,
            'details'          => 'আপনার রেফার করা বন্ধুকে sManager অ্যাপ ২৫ দিন ব্যাবহার করতে হবে।'
        ],
        [
            'step'             => '৪র্থ',
            'amount'           => 100,
            'duration'         => 999999999999999999999999999,
            'nid_verification' => true,
            'details'          => 'আপনার বন্ধুকে sManager অ্যাপের মাধ্যমে NID ভেরিফিকেশন করতে হবে।'
        ]
    ],
    'refer_details_faq'=>[
        'title'=>'আপনি দ্রুত রেফারের টাকা পেতে -',
        'details'=>[
            ''
        ]
    ]
];
