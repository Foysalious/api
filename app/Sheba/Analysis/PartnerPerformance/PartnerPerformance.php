<?php namespace Sheba\Analysis\PartnerPerformance;

use App\Models\Partner;
use Sheba\Helpers\TimeFrame;

class PartnerPerformance
{
    /** @var TimeFrame */
    private $timeFrame;

    /** @var Partner */
    private $partner;

    public function __construct()
    {

    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setTimeFrame(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    public function get()
    {
        return [
            'score' => 3.5,
            'performance_summary' => [
                'total_order_taken' => 51,
                'successfully_completed' => 39,
                'order_without_complain' => 30,
                'timely_order_taken' => 46,
                'timely_job_start' => 15
            ],
            'successfully_completed' => [
                'total_order' => 24,
                'rate' => 49,
                'last_week_rate' => 34,
                'is_improved' => 1,
                'last_week_rate_difference' => 15,
                'last_weeks' => [
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ]
                ]
            ],
            'order_without_complain' => [
                'total_order' => 30,
                'rate' => 60,
                'last_week_rate' => 54,
                'is_improved' => 1,
                'last_week_rate_difference' => 6,
                'last_weeks' => [
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ]
                ]
            ],
            'timely_order_taken' => [
                'total_order' => 46,
                'rate' => 93,
                'last_week_rate' => 95,
                'is_improved' => 0,
                'last_week_rate_difference' => 2,
                'last_weeks' => [
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ]
                ]
            ],
            'timely_job_start' => [
                'total_order' => 15,
                'rate' => 30,
                'last_week_rate' => 47,
                'is_improved' => 0,
                'last_week_rate_difference' => 17,
                'last_weeks' => [
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ],
                    [
                        'name' => 'Week 41',
                        'date_range' => [
                            'start' => '2018-11-11',
                            'end' => '2018-11-18'
                        ],
                        'value' => '12',
                    ]
                ]
            ]
        ];
    }
}