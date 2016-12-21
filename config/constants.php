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
        'Not Responded' => 'Not Responded',
        'Schedule Due' => 'Schedule Due',
        'Process' => 'Process',
        'Served' => 'Served',
        'Cancelled' => 'Cancelled'
    ],
    'JOB_STATUSES_SHOW' => [
        'Pending' => ['sheba'=> 'Pending', 'partner' => 'Pending', 'customer' => 'Pending'],
        'Accepted' => ['sheba'=> 'Accepted', 'partner' => 'Accepted', 'customer' => 'Accepted'],
        'Declined' => ['sheba'=> 'Declined', 'partner' => 'Declined', 'customer' => 'Declined'],
        'Not Responded' => ['sheba'=> 'Not Responded', 'partner' => 'Not Responded', 'customer' => 'Not Responded'],
        'Schedule Due' => ['sheba'=> 'Schedule Due', 'partner' => 'Schedule Due', 'customer' => 'Schedule Due'],
        'Process' => ['sheba'=> 'Process', 'partner' => 'Process', 'customer' => 'Process'],
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
        'Process' => ['sheba'=> 'Process', 'partner' => 'Process', 'customer' => 'Process'],
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
        'Process' => ['sheba'=> 'Process', 'partner' => 'Process', 'customer' => 'Process'],
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
    'JOB_PREFERRED_TIMES' => [
        '10.00 A.M. - 01.00 P.M.' => '10.00 A.M. - 01.00 P.M.',
        '01.00 P.M. - 05.00 P.M.' => '01.00 P.M. - 05.00 P.M.',
        '05.00 P.M. - 09.00 P.M.' => '05.00 P.M. - 09.00 P.M.',
        'Anytime' => 'Anytime',
    ],
];