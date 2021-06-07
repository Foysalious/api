<?php namespace Sheba\Reward;

use App\Models\Reward;

class EventDataConverter
{
    private $event;

    public function __construct()
    {
        $this->event = collect([
            'partner' => [
                'action' => [
                    'rating' => [
                        'name' => 'Rating',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\Rating\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\Rating\Rule',
                        'parameters' => [
                            'rate' => [
                                'type' => 'number',
                                'min' => 0
                            ]
                        ]
                    ],
                    'partner_wallet_recharge' => [
                        'name' => 'Partner Wallet Recharge',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\WalletRecharge\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\WalletRecharge\Rule',
                        'parameters' => [
                            'amount' => [
                                'type' => 'number',
                                'min' => 0,
                                'class' => 'Sheba\Reward\Event\Partner\Action\WalletRecharge\Parameter\Amount'
                            ]
                        ]
                    ],
                    'order_serve' => [
                        'name' => 'Order Serve',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\OrderServed\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\OrderServed\Rule',
                        'parameters' => [
                            'amount' => [
                                'type' => 'number',
                                'min' => 0,
                                'class' => 'Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\Amount'
                            ],
                            'portals' => [
                                'type' => 'select',
                                'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                                'is_multi_selectable' => 1,
                                'class' => 'Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\Portal'
                            ],
                            'excluded_status' => [
                                'type' => 'select',
                                'possible_value' => [
                                    'Not_Responded' => 'Not Responded',
                                    'Schedule_Due' => 'Schedule Due',
                                    'Serve_Due' => 'Serve Due'
                                ],
                                'is_multi_selectable' => 1,
                                'class' => 'Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\ExcludedStatus'
                            ],
                            'created_from' => [
                                'type' => 'select',
                                'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                                'is_multi_selectable' => 1,
                                'class' => 'Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\CreatedFrom'
                            ],
                            'sales_channels' => [
                                'type' => 'select',
                                'possible_value' => getSalesChannels(),
                                'is_multi_selectable' => 1,
                                'class' => 'Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\SalesChannel'
                            ],
                            'excluded_sales_channels' => [
                                'type' => 'select',
                                'possible_value' => getSalesChannels(),
                                'is_multi_selectable' => 1,
                                'class' => 'Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\ExcludedSalesChannel'
                            ]
                        ]
                    ],
                    'partner_creation_bonus' => [
                        'name' => 'Partner Creation Bonus',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\PartnerCreationBonus\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\PartnerCreationBonus\Rule',
                        'parameters' => [
                            'registration_channel' => [
                                'type' => 'select',
                                'possible_value' => constants('PARTNER_ACQUISITION_CHANNEL'),
                                'is_multi_selectable' => 1
                            ]
                        ]
                    ],
                    'pos_inventory_create' => [
                        'name' => 'Pos Inventory Create',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\PosInventoryCreate\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\PosInventoryCreate\Rule',
                        'parameters' => [
                            'created_from' => [
                                'type' => 'select',
                                'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                                'is_multi_selectable' => 1,
                                'class' => 'Sheba\Reward\Event\Partner\Action\PosInventoryCreate\Parameter\CreatedFrom'
                            ]
                        ]
                    ],
                    'pos_customer_create' => [
                        'name' => 'Pos Customer Create',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\PosCustomerCreate\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\PosCustomerCreate\Rule',
                        'parameters' => [
                            'created_from' => [
                                'type' => 'select',
                                'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                                'is_multi_selectable' => 1,
                                'class' => 'Sheba\Reward\Event\Partner\Action\PosCustomerCreate\Parameter\CreatedFrom'
                            ]
                        ]
                    ],
                    'daily_usage' => [
                        'name' => 'Daily Usage',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\DailyUsage\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\DailyUsage\Rule',
                        'parameters' => [
                            'count' => [
                                'type' => 'number',
                                'min' => 0,
                                'class' => 'Sheba\Reward\Event\Partner\Action\DailyUsage\Parameter\Count'
                            ],
                            'created_from' => [
                                'type' => 'select',
                                'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                                'is_multi_selectable' => 1,
                                'class' => 'Sheba\Reward\Event\Partner\Action\DailyUsage\Parameter\CreatedFrom'
                            ]

                        ]
                    ],
                    'payment_link_usage' => [
                        'name' => 'Payment Link Usage',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\PaymentLinkUsage\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\PaymentLinkUsage\Rule',
                        'parameters' => [
                            'amount' => [
                                'type' => 'number',
                                'min' => 0,
                                'class' => 'Sheba\Reward\Event\Partner\Action\PaymentLinkUsage\Parameter\Amount'
                            ],
                        ]
                    ],
                    'pos_order_create' => [
                        'name' => 'Pos Order Create',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\PosOrderCreate\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\PosOrderCreate\Rule',
                        'parameters' => [
                            'amount' => [
                                'type' => 'number',
                                'min' => 0,
                                'class' => 'Sheba\Reward\Event\Partner\Action\PosOrderCreate\Parameter\Amount'
                            ],
                            'created_from' => [
                                'type' => 'select',
                                'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                                'is_multi_selectable' => 1,
                                'class' => 'Sheba\Reward\Event\Partner\Action\PosOrderCreate\Parameter\CreatedFrom'
                            ]
                        ]
                    ],
                    'top_up' => [
                        'name' => 'Top Up',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\TopUp\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\TopUp\Rule',
                        'parameters' => [
                            'amount' => [
                                'type' => 'number',
                                'min' => 1,
                                'class' => 'Sheba\Reward\Event\Partner\Action\TopUp\Parameter\Amount'
                            ]
                        ]
                    ]
                ],
                'campaign' => [
                    'order_serve' => [
                        'name' => 'Order Serve',
                        'event_class' => 'Sheba\Reward\Event\Partner\Campaign\OrderServed\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Campaign\OrderServed\Rule',
                        'parameters' => [
                            'target' => [
                                'type' => 'number',
                                'min' => 0
                            ],
                            'portals' => [
                                'type' => 'select',
                                'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                                'is_multi_selectable' => 1
                            ],
                            'excluded_status' => [
                                'type' => 'select',
                                'possible_value' => [
                                    'Not_Responded' => 'Not Responded',
                                    'Schedule_Due' => 'Schedule Due',
                                    'Serve_Due' => 'Serve Due'
                                ],
                                'is_multi_selectable' => 1
                            ]
                        ]
                    ],
                    'pos_entry' => [
                        'name' => 'Pos Entry',
                        'event_class' => 'Sheba\Reward\Event\Partner\Campaign\PosEntry\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Campaign\PosEntry\Rule',
                        'parameters' => [
                            'target' => [
                                'type' => 'number',
                                'min' => 0
                            ],
                        ]
                    ],
                    'due_entry' => [
                        'name' => 'Due Entry',
                        'event_class' => 'Sheba\Reward\Event\Partner\Campaign\DueTrackerEntry\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Campaign\DueTrackerEntry\Rule',
                        'parameters' => [
                            'target' => [
                                'type' => 'number',
                                'min' => 0
                            ],
                        ]
                    ],
                ]
            ],
            'customer' => [
                'action' => [
                    'wallet_cashback' => [
                        'name' => 'Wallet Cashback',
                        'event_class' => 'Sheba\Reward\Event\Customer\Action\WalletCashback\Event',
                        'rule_class' => 'Sheba\Reward\Event\Customer\Action\WalletCashback\Rule',
                        'parameters' => []
                    ],
                    'order_serve_and_paid' => [
                        'name' => 'Order Serve And Paid',
                        'event_class' => 'Sheba\Reward\Event\Customer\Action\OrderServedAndPaid\Event',
                        'rule_class' => 'Sheba\Reward\Event\Customer\Action\OrderServedAndPaid\Rule',
                        'parameters' => [
                            'amount' => [
                                'type' => 'number',
                                'min' => 0
                            ],
                            'sales_channels' => [
                                'type' => 'select',
                                'possible_value' => getSalesChannels(),
                                'is_multi_selectable' => 1
                            ],
                            'payment_methods' => [
                                'type' => 'select',
                                'possible_value' => ['Bkash', 'Cash On Delivery', 'Online', 'Ssl', 'Wallet'],
                                'is_multi_selectable' => 1
                            ]
                        ]
                    ],
                    'profile_complete' => [
                        'name' => 'Completed Profile',
                        'event_class' => 'Sheba\Reward\Event\Customer\Action\ProfileComplete\Event',
                        'rule_class' => 'Sheba\Reward\Event\Customer\Action\ProfileComplete\Rule',
                        'parameters' => []
                    ]
                ],
                'campaign' => []
            ],
            'resource' => [
                'campaign' =>
                    [
                        'order_serve' => [
                            'name' => 'Order Serve',
                            'event_class' => 'Sheba\Reward\Event\Resource\Campaign\OrderServed\Event',
                            'rule_class' => 'Sheba\Reward\Event\Resource\Campaign\OrderServed\Rule',
                            'parameters' => [
                                'target' => [
                                    'type' => 'number',
                                    'min' => 0
                                ],
                                'portals' => [
                                    'type' => 'select',
                                    'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                                    'is_multi_selectable' => 1
                                ],
                                'excluded_status' => [
                                    'type' => 'select',
                                    'possible_value' => [
                                        'Not_Responded' => 'Not Responded',
                                        'Schedule_Due' => 'Schedule Due',
                                        'Serve_Due' => 'Serve Due'
                                    ],
                                    'is_multi_selectable' => 1
                                ],
                                'rating' => [
                                    'type' => 'number',
                                    'min' => 1
                                ],
                                'five_star_rating' => [
                                    'type' => 'select',
                                    'possible_value' => [
                                        'with_compliment' => 'with_complement',
                                        'without_compliment' => 'without_complement',
                                    ],
                                    'is_multi_selectable' => 0
                                ],
                                'complain_ratio' => [
                                    'type' => 'number',
                                    'min' => 0
                                ],
                                'serve_ratio_from_spro' => [
                                    'type' => 'number',
                                    'min' => 0
                                ],
                                'rating_point_ratio' => [
                                    'type' => 'number',
                                    'min' => 1
                                ],
                                'gmv' => [
                                    'type' => 'number',
                                    'min' => 0
                                ],
                            ]
                        ]
                    ],
                'action' => [
                    'info_call_completed' => [
                        'name' => 'InfoCall to Order Served and Paid',
                        'event_class' => 'Sheba\Reward\Event\Resource\Action\InfoCallCompleted\Event',
                        'rule_class' => 'Sheba\Reward\Event\Resource\Action\InfoCallCompleted\Rule',
                        'parameters' => [
                            'amount' => [
                                'type' => 'number',
                                'min' => 0,
                                'class' => 'Sheba\Reward\Event\Resource\Action\InfoCallCompleted\Parameter\Amount'
                            ],
                            'create_portal'=> [
                                'type' => 'select',
                                'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                                'is_multi_selectable' => 0,
                                'class' => 'Sheba\Reward\Event\Resource\Action\InfoCallCompleted\Parameter\CreatePortal'
                            ],
                            'serve_portal' => [
                                'type' => 'select',
                                'possible_value'=> indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                                'is_multi_selectable' => 1,
                                'class' => 'Sheba\Reward\Event\Resource\Action\InfoCallCompleted\Parameter\ServePortal'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function getEventClass(Reward $reward, $detail_name)
    {
        $target_type = $this->formatTargetType($reward->target_type);
        $detail_type = $this->formatDetailType($reward->detail_type);

        return $this->event[$target_type][$detail_type][$detail_name]['event_class'];
    }

    public function getRuleClass(Reward $reward, $detail_name)
    {
        $target_type = $this->formatTargetType($reward->target_type);
        $detail_type = $this->formatDetailType($reward->detail_type);

        return $this->event[$target_type][$detail_type][$detail_name]['rule_class'];
    }

    public function getParams(Reward $reward, $detail_name)
    {
        $target_type = $this->formatTargetType($reward->target_type);
        $detail_type = $this->formatDetailType($reward->detail_type);

        return $this->event[$target_type][$detail_type][$detail_name]['parameters'];
    }

    private function formatTargetType($target_type)
    {
        return strtolower(str_replace('App\Models\\', '', $target_type));
    }

    private function formatDetailType($detail_type)
    {
        return strtolower(str_replace('App\Models\Reward', '', $detail_type));
    }

    public function getAll()
    {
        return $this->event;
    }

    public function getAllFor($target_type)
    {
        return $this->event[$target_type];
    }

    /**
     * @param $target_type | partner or customer
     * @param $event_type | campaign or action
     * @return mixed
     */
    public function getEventsFor($target_type, $event_type)
    {
        return $this->event[$target_type][$event_type];
    }
}

// 01. possible_value for parameters should be associative array (JS object)