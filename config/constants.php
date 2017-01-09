<?php

return [
    'HOTLINE' => '09639 - 444 000',
    'SERVICE_VARIABLE_TYPES' => ['Fixed' => 'Fixed', 'Options' => 'Options', 'Custom' => 'Custom'],
    'PARTNER_STATUSES' => ['Verified' => 'Verified', 'Unverified' => 'Unverified', 'Paused' => 'Paused'],
    'PARTNER_LEVELS' => ['Starter', 'Intermediate', 'Advanced'],
    'PARTNER_TYPES' => ['USP', 'NSP', 'ESP'],
    'RESOURCE_TYPES' => ['Admin' => 'Admin', 'Operation' => 'Operation', 'Finance' => 'Finance', 'Handyman' => 'Handyman'],
    'JOB_STATUSES' => [
        'Pending' => 'Pending',
        'Accepted' => 'Accepted',
        'Declined' => 'Declined',
        'Not_Responded' => 'Not Responded',
        'Schedule_Due' => 'Schedule Due',
        'Process' => 'Process',
        'Served' => 'Served',
        'Cancelled' => 'Cancelled'
    ],
    'JOB_STATUSES_SHOW' => [
        'Pending' => ['sheba'=> 'Pending', 'partner' => 'Pending', 'customer' => 'Pending'],
        'Accepted' => ['sheba'=> 'Accepted', 'partner' => 'Accepted', 'customer' => 'Accepted'],
        'Declined' => ['sheba'=> 'Declined', 'partner' => 'Declined', 'customer' => 'Pending'],
        'Not_Responded' => ['sheba'=> 'Not Responded', 'partner' => 'Not Responded', 'customer' => 'Response Delay'],
        'Schedule_Due' => ['sheba'=> 'Schedule Due', 'partner' => 'Schedule Due', 'customer' => 'Behind Schedule'],
        'Process' => ['sheba'=> 'Process', 'partner' => 'Process', 'customer' => 'On Going'],
        'Served' => ['sheba'=> 'Served', 'partner' => 'Served', 'customer' => 'Served'],
        'Cancelled' => ['sheba'=> 'Cancelled', 'partner' => 'Cancelled', 'customer' => 'Cancelled']
    ],
    'PARTNER_ORDER_STATUSES' => [
        'Open' => 'Open',
        'Process' => 'Process',
        'Closed' => 'Closed',
        'Cancelled' => 'Cancelled'
    ],
    'PARTNER_ORDER_STATUSES_SHOW' => [
        'Open' => ['sheba'=> 'Open', 'partner' => 'Open', 'customer' => 'Open'],
        'Process' => ['sheba'=> 'Process', 'partner' => 'Process', 'customer' => 'On Going'],
        'Closed' => ['sheba'=> 'Closed', 'partner' => 'Closed', 'customer' => 'Closed'],
        'Cancelled' => ['sheba'=> 'Cancelled', 'partner' => 'Cancelled', 'customer' => 'Cancelled']
    ],
    'ORDER_STATUSES' => [
        'Open' => 'Open',
        'Process' => 'Process',
        'Closed' => 'Closed',
        'Cancelled' => 'Cancelled'
    ],
    'ORDER_STATUSES_SHOW' => [
        'Open' => ['sheba'=> 'Open', 'partner' => 'Open', 'customer' => 'Open'],
        'Process' => ['sheba'=> 'Process', 'partner' => 'Process', 'customer' => 'On Going'],
        'Closed' => ['sheba'=> 'Closed', 'partner' => 'Closed', 'customer' => 'Closed'],
        'Cancelled' => ['sheba'=> 'Cancelled', 'partner' => 'Cancelled', 'customer' => 'Cancelled']
    ],
    'FLAG_STATUSES' => [
        'Open' => 'Open',
        'Acknowledged' => 'Acknowledged',
        'Paused' => 'Paused',
        'Solved' => 'Solved'
    ],
    'PRIORITY_LEVELS' => ['Green' => 'Green', 'Amber' => 'Amber', 'Red' => 'Red'],
    'ALT_PRIORITY_LEVELS' => ['Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High'],
    'JOB_PREFERRED_TIMES' => [
        '10.00 A.M. - 01.00 P.M.' => '10.00 A.M. - 01.00 P.M.',
        '01.00 P.M. - 05.00 P.M.' => '01.00 P.M. - 05.00 P.M.',
        '05.00 P.M. - 09.00 P.M.' => '05.00 P.M. - 09.00 P.M.',
        'Anytime' => 'Anytime',
    ],

    'JOB_CI_LEVELS' => ['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low'],
    'JOB_SATISFACTION_LEVELS' => ['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low'],

    'JOB_CANCEL_REASONS' => [
        'Customer Dependency'   => 'Customer Dependency',
        'Customer Management'   => 'Customer Management',
        'Push Sales Attempt'    => 'Push Sales Attempt',
        'Insufficient Partner'  => 'Insufficient Partner',
        'Price Shock'           => 'Price Shock',
        'Service Limitation'    => 'Service Limitation',
    ],
];