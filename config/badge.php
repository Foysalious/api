<?php

return  [
    //Badges with weights
    'badges' => [
        'gold' => 2,
        'silver' => 1
    ],
    'params' => [
        'total_jobs' => [
            'value' => [
                'gold' => 40,
                'silver' => 20
            ],
            'operator' => '>='
        ],
        'sum_of_ratings' => [
            'value' => [
                'gold' => 40,
                'silver' => 12
            ],
            'operator' => '>='
        ],
        'avg_rating' => [
            'value' => [
                'gold' => 4.75,
                'silver' => 4.5
            ],
            'operator' => '>='
        ],
        'number_of_days_with_sheba' => [
            'value' => [
                'gold' => 60,
                'silver' => 30
            ],
            'operator' => '>='
        ],
        'without_complaint_ratio' => [
            'value' => [
                'gold' => .98,
                'silver' => .95
            ],
            'operator' => '>='
        ],
        'serve_ratio' => [
            'value' => [
                'gold' => .90,
                'silver' => .80
            ],
            'operator' => '>='
        ],
        'on_time_job_ratio' => [
            'value' => [
                'gold' => .80,
                'silver' => .70
            ],
            'operator' => '>='
        ],
        'package' => [
            'value' => [
                'gold' => 'ESP',
                'silver' => 'PSP'
            ],
            'operator' => '=='
        ],
    ],
];