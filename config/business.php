<?php

return [
    'actions' => [
        'trip_request' => [
            'auto_assign' => 'trip_request_auto_assign',
            'rw' => 'trip_request_rw'
        ],
        'inspection' => [
            'rw' => 'inspection_rw'
        ],
    ],
    'DRIVER_CONTRACT_TYPES' => ['permanent', 'temporary'],
    'VEHICLE_TYPES' => ['hatchback', 'sedan', 'suv', 'passenger_van', 'others'],
    'INSPECTION_TYPE' => [
        'one_time' => 'One Time',
        'monthly' => 'Monthly',
        'weekly' => 'Weekly',
    ]
];