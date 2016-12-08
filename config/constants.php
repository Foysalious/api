<?php

return [
    'HOTLINE' => '09639 - 444 000',
    'SERVICE_VARIABLE_TYPES' => ['Fixed' => 'Fixed', 'Options' => 'Options', 'Custom' => 'Custom'],
    'PARTNER_STATUSES' => ['Verified' => 'Verified', 'Unverified' => 'Unverified', 'Paused' => 'Paused'],
    'PARTNER_LEVELS' => ['Starter', 'Intermediate', 'Advanced'],
    'PARTNER_TYPES' => ['USP', 'NSP', 'ESP'],
    'RESOURCE_TYPES' => ['Admin' => 'Admin', 'Operation' => 'Operation', 'Finance' => 'Finance', 'Handyman' => 'Handyman'],
    'JOB_STATUSES' => [
        'Open' => 'Open',
        'Checked' => 'Checked',
        'Assigned' => 'Assigned',
        'Locked' => 'Locked',
        'Delivered' => 'Delivered',
        'Paid' => 'Paid',
        'Closed' => 'Closed',
        'Cancelled' => 'Cancelled'
    ],
    'JOB_STATUSES_SHOW' => [
        'Open' => ['sheba'=> 'Open', 'partner' => 'Open', 'customer' => 'Open'],
        'Checked' => ['sheba'=> 'Checked', 'partner' => 'Checked', 'customer' => 'Checked'],
        'Assigned' => ['sheba'=> 'Assigned', 'partner' => 'Assigned', 'customer' => 'Assigned'],
        'Locked' => ['sheba'=> 'Locked', 'partner' => 'Locked', 'customer' => 'Locked'],
        'Delivered' => ['sheba'=> 'Delivered', 'partner' => 'Delivered', 'customer' => 'Delivered'],
        'Paid' => ['sheba'=> 'Paid', 'partner' => 'Paid', 'customer' => 'Paid'],
        'Closed' => ['sheba'=> 'Closed', 'partner' => 'Closed', 'customer' => 'Closed'],
        'Cancelled' => ['sheba'=> 'Cancelled', 'partner' => 'Cancelled', 'customer' => 'Cancelled']
    ]
];