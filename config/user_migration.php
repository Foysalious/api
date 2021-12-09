<?php

return [
    'modules' => [
        [
            'key'           => 'expense', //accounting - expense given by mobile team
            'app_version'   => 240000,
            'status'        => null,
            'priority'      => 1
        ],
        [
            'key'           => 'pos',
            'app_version'   => 240000,
            'status'        => null,
            'priority'      => 2
        ]
    ],
    // TODO: Need to remove in future. For Mobile Team Testing Purpose
    'modules_for_test' => [
    [
        'key'           => 'pos',
        'app_version'   => 400000,
        'status'        => null,
        'priority'      => 1
    ],
    [
        'key'           => 'expense',
        'app_version'   => 230600,
        'status'        => null,
        'priority'      => 2
    ]
]

];