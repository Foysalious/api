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
                                'type'  => 'number',
                                'min'   => 0
                            ]
                        ]
                    ],
                    'partner_wallet_recharge' => [
                        'name' => 'Partner Wallet Recharge',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\WalletRecharge\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\WalletRecharge\Rule',
                        'parameters' => [
                            'amount' => [
                                'type'  => 'number',
                                'min'   => 0
                            ]
                        ]
                    ],
                    'order_serve' => [
                        'name' => 'Order Serve',
                        'event_class' => 'Sheba\Reward\Event\Partner\Action\OrderServed\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Action\OrderServed\Rule',
                        'parameters' => [
                            'amount' => [
                                'type'  => 'number',
                                'min'   => 0
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
                                    'Schedule_Due'  => 'Schedule Due',
                                    'Serve_Due'     => 'Serve Due'
                                ],
                                'is_multi_selectable' => 1
                            ],
                            'created_from' => [
                                'type' => 'select',
                                'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                                'is_multi_selectable' => 1
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
                    ]
                ],
                'campaign' => [
                    'order_serve' => [
                        'name' => 'Order Serve',
                        'event_class' => 'Sheba\Reward\Event\Partner\Campaign\OrderServed\Event',
                        'rule_class' => 'Sheba\Reward\Event\Partner\Campaign\OrderServed\Rule',
                        'parameters' => [
                            'target' => [
                                'type'  => 'number',
                                'min'   => 0
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
                                    'Schedule_Due'  => 'Schedule Due',
                                    'Serve_Due'     => 'Serve Due'
                                ],
                                'is_multi_selectable' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'customer' => [
                'action'    => [
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
                                'type'  => 'number',
                                'min'   => 0
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
                    ]
                ],
                'campaign'  => []
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