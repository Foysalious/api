<?php namespace Sheba\Reward;

use App\Models\Reward;
use App\Models\TopUpVendor;
use Carbon\Carbon;
use Sheba\Payment\AvailableMethods;

class EventDataConverter
{
    private $event;
    private $operator;

    public function __construct()
    {
        $this->setOperators();

        $this->event = collect([
            'partner' => $this->getPartnerEvents(),
            'customer' => $this->getCustomerEvents(),
            'resource' => $this->getResourceEvents(),
            'affiliate' => $this->getAffiliateEvents()
        ]);
    }

    private function getPartnerEvents()
    {
        return [
            'action' => $this->getPartnerActions(),
            'campaign' => $this->getPartnerCampaigns()
        ];
    }

    private function getPartnerCampaigns()
    {
        $wallet_recharge_gateways = AvailableMethods::getWalletRechargePayments();
        $wallet_recharge_gateways = array_combine($wallet_recharge_gateways, $wallet_recharge_gateways);
        return [
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
            'top_up' => [
                'name' => 'Top Up',
                'event_class' => \Sheba\Reward\Event\Partner\Campaign\Topup\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Campaign\Topup\Rule::class,
                'parameters' => [
                    'amount_greater_than_equal' => [
                        'type' => 'number',
                        'min' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Campaign\Topup\Parameter\AmountGreaterThanEqual::class
                    ],
                    'amount_smaller_than_equal' => [
                        'type' => 'number',
                        'min' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Campaign\Topup\Parameter\AmountSmallerThanEqual::class
                    ],
                    'operator' => [
                        'type' => 'select',
                        'possible_value' => $this->operator,
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Campaign\Topup\Parameter\Operator::class
                    ],
                    'target' => [
                        'type' => 'number',
                        'min' => 1
                    ]
                ]
            ],
            'topup_otf' => [
                'name' => 'Top Up OTF',
                'event_class' => \Sheba\Reward\Event\Partner\Campaign\TopupOTF\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Campaign\TopupOTF\Rule::class,
                'parameters' => [
                    'otf_amount' => [
                        'type' => 'number',
                        'min' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Campaign\TopupOTF\Parameter\OTFAmount::class
                    ],
                    'operator' => [
                        'type' => 'select',
                        'possible_value' => $this->operator,
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Campaign\TopupOTF\Parameter\Operator::class
                    ],
                    'sim_type' => [
                        'type' => 'select',
                        'possible_value' => ['prepaid' => 'Prepaid', 'postpaid' => 'Postpaid'],
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Campaign\TopupOTF\Parameter\SimType::class
                    ],
                    'target' => [
                        'type' => 'number',
                        'min' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Campaign\TopupOTF\Parameter\Target::class
                    ],
                ]
            ],
            'wallet_recharge' => [
                'name' => 'Wallet Recharge',
                'event_class' => \Sheba\Reward\Event\Partner\Campaign\WalletRecharge\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Campaign\WalletRecharge\Rule::class,
                'parameters' => [
                    'amount_greater_than_equal' => [
                        'type' => 'number',
                        'min' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Campaign\WalletRecharge\Parameter\AmountGreaterThan::class
                    ],
                    'amount_smaller_than_equal' => [
                        'type' => 'number',
                        'min' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Campaign\WalletRecharge\Parameter\AmountSmallerThan::class
                    ],
                    'gateway' => [
                        'type' => 'select',
                        'possible_value' => $wallet_recharge_gateways,
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Campaign\WalletRecharge\Parameter\Gateway::class
                    ],
                    'target' => [
                        'type' => 'number',
                        'min' => 0
                    ]
                ]
            ],
            'consecutive_topup' => [
                'name' => 'Consecutive Days of Top Up',
                'event_class' => \Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp\Rule::class,
                'parameters' => [
                    'last_usage' => [
                        'type' => 'usage',
                        'class' => \Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp\Parameter\LastUsage::class,
                        'day_count_min' => 1,
                        'start_max' => Carbon::yesterday()->toDateString(),
                        'end_max' => Carbon::yesterday()->toDateString(),
                    ],
                    'target' => [
                        'type' => 'number',
                        'class' => \Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp\Parameter\Target::class,
                        'min' => 0
                    ]
                ]
            ]
        ];
    }

    private function getPartnerActions()
    {
        return [
            'rating' => [
                'name' => 'Rating',
                'event_class' => \Sheba\Reward\Event\Partner\Action\Rating\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Action\Rating\Rule::class,
                'parameters' => [
                    'rate' => [
                        'type' => 'number',
                        'min' => 0
                    ]
                ]
            ],
            'wallet_recharge' => [
                'name' => 'Partner Wallet Recharge',
                'event_class' => \Sheba\Reward\Event\Partner\Action\WalletRecharge\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Action\WalletRecharge\Rule::class,
                'parameters' => [
                    'amount' => [
                        'type' => 'number',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Partner\Action\WalletRecharge\Parameter\Amount::class
                    ]
                ]
            ],
            'order_serve' => [
                'name' => 'Order Serve',
                'event_class' => \Sheba\Reward\Event\Partner\Action\OrderServed\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Action\OrderServed\Rule::class,
                'parameters' => [
                    'amount' => [
                        'type' => 'number',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\Amount::class
                    ],
                    'portals' => [
                        'type' => 'select',
                        'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\Portal::class
                    ],
                    'excluded_status' => [
                        'type' => 'select',
                        'possible_value' => [
                            'Not_Responded' => 'Not Responded',
                            'Schedule_Due' => 'Schedule Due',
                            'Serve_Due' => 'Serve Due'
                        ],
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\ExcludedStatus::class
                    ],
                    'created_from' => [
                        'type' => 'select',
                        'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\CreatedFrom::class
                    ],
                    'sales_channels' => [
                        'type' => 'select',
                        'possible_value' => getSalesChannels(),
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\SalesChannel::class
                    ],
                    'excluded_sales_channels' => [
                        'type' => 'select',
                        'possible_value' => getSalesChannels(),
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\ExcludedSalesChannel::class
                    ]
                ]
            ],
            'partner_creation_bonus' => [
                'name' => 'Partner Creation Bonus',
                'event_class' => \Sheba\Reward\Event\Partner\Action\PartnerCreationBonus\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Action\PartnerCreationBonus\Rule::class,
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
                'event_class' => \Sheba\Reward\Event\Partner\Action\PosInventoryCreate\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Action\PosInventoryCreate\Rule::class,
                'parameters' => [
                    'created_from' => [
                        'type' => 'select',
                        'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\PosInventoryCreate\Parameter\CreatedFrom::class
                    ]
                ]
            ],
            'pos_customer_create' => [
                'name' => 'Pos Customer Create',
                'event_class' => \Sheba\Reward\Event\Partner\Action\PosCustomerCreate\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Action\PosCustomerCreate\Rule::class,
                'parameters' => [
                    'created_from' => [
                        'type' => 'select',
                        'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\PosCustomerCreate\Parameter\CreatedFrom::class
                    ]
                ]
            ],
            'daily_usage' => [
                'name' => 'Daily Usage',
                'event_class' => \Sheba\Reward\Event\Partner\Action\DailyUsage\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Action\DailyUsage\Rule::class,
                'parameters' => [
                    'count' => [
                        'type' => 'number',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Partner\Action\DailyUsage\Parameter\Count::class
                    ],
                    'created_from' => [
                        'type' => 'select',
                        'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\DailyUsage\Parameter\CreatedFrom::class
                    ]

                ]
            ],
            'payment_link_usage' => [
                'name' => 'Payment Link Usage',
                'event_class' => \Sheba\Reward\Event\Partner\Action\PaymentLinkUsage\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Action\PaymentLinkUsage\Rule::class,
                'parameters' => [
                    'amount' => [
                        'type' => 'number',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Partner\Action\PaymentLinkUsage\Parameter\Amount::class
                    ],
                ]
            ],
            'pos_order_create' => [
                'name' => 'Pos Order Create',
                'event_class' => \Sheba\Reward\Event\Partner\Action\PosOrderCreate\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Action\PosOrderCreate\Rule::class,
                'parameters' => [
                    'amount' => [
                        'type' => 'number',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Partner\Action\PosOrderCreate\Parameter\Amount::class
                    ],
                    'created_from' => [
                        'type' => 'select',
                        'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\PosOrderCreate\Parameter\CreatedFrom::class
                    ]
                ]
            ],
            'top_up' => [
                'name' => 'Top Up',
                'event_class' => \Sheba\Reward\Event\Partner\Action\TopUp\Event::class,
                'rule_class' => \Sheba\Reward\Event\Partner\Action\TopUp\Rule::class,
                'parameters' => [
                    'amount_greater_than' => [
                        'type' => 'number',
                        'min' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\TopUp\Parameter\AmountGreaterThan::class
                    ],
                    'fixed_amount' => [
                        'type' => 'number',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Partner\Action\TopUp\Parameter\FixedAmount::class
                    ],
                    'operator' => [
                        'type' => 'select',
                        'possible_value' => $this->operator,
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\TopUp\Parameter\Operator::class
                    ],
                    'lifetime_topup_count' => [
                        'type' => 'number',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Partner\Action\TopUp\Parameter\LifetimeTopupCount::class
                    ],
                    'topup_day_count' => [
                        'type' => 'range',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Partner\Action\TopUp\Parameter\TopupDayCount::class
                    ],
                    'no_topup_day_count' => [
                        'type' => 'number',
                        'min' => 1,
                        'class' => \Sheba\Reward\Event\Partner\Action\TopUp\Parameter\NoTopupDayCount::class
                    ]
                ]
            ]
        ];
    }

    private function getCustomerEvents()
    {
        return [
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
            ]
        ];
    }

    private function getResourceEvents()
    {
        return [
            'campaign' => $this->getResourceCampaigns(),
            'action' => $this->getResourceActions()
        ];
    }

    private function getResourceActions()
    {
        return [
            'order_serve' => [
                'name' => 'Order Serve',
                'event_class' => 'Sheba\Reward\Event\Resource\Action\OrderServed\Event',
                'rule_class' => 'Sheba\Reward\Event\Resource\Action\OrderServed\Rule',
                'parameters' => [
                    'amount' => [
                        'type' => 'number',
                        'min' => 0,
                        'class' => 'Sheba\Reward\Event\Resource\Action\OrderServed\Parameter\Amount'
                    ],
                    'portals' => [
                        'type' => 'select',
                        'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                        'is_multi_selectable' => 1,
                        'class' => 'Sheba\Reward\Event\Resource\Action\OrderServed\Parameter\Portal'
                    ],
                    'create_portals' => [
                        'type' => 'select',
                        'possible_value' => indexedArrayToAssociative(config('sheba.portals'), config('sheba.portals')),
                        'is_multi_selectable' => 1,
                        'class' => 'Sheba\Reward\Event\Resource\Action\OrderServed\Parameter\CreatePortal'
                    ]
                ]
            ],
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
        ];
    }

    private function getResourceCampaigns()
    {
        return [
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
        ];
    }

    private function getAffiliateEvents()
    {
        return [
            'campaign' => $this->getAffiliateCampaigns(),
            'action' => $this->getAffiliateActions(),
        ];
    }

    private function getAffiliateCampaigns()
    {
        return [
            'topup' => [
                'name' => 'TopUp',
                'event_class' => 'Sheba\Reward\Event\Affiliate\Campaign\Topup\Event',
                'rule_class' => 'Sheba\Reward\Event\Affiliate\Campaign\Topup\Rule',
                'parameters' => [
                    'target' => [
                        'type' => 'number',
                        'min' => 0
                    ],
                    'topup_status' => [
                        'type' => 'select',
                        'possible_value' => ['Successful' => 'Successful'],
                        'is_multi_selectable' => 0
                    ],
                    'operator' => [
                        'type' => 'select',
                        'possible_value' => $this->operator,
                        'is_multi_selectable' => 1
                    ]
                ]
            ],

            'topup_otf' => [
                'name' => 'TopUp-OTF',
                'event_class' => 'Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Event',
                'rule_class' => 'Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Rule',
                'parameters' => [
                    'target' => [
                        'type' => 'number',
                        'min' => 0
                    ],
                    'quantity' => [
                        'type' => 'number',
                        'min' => 0,
                        'warning' => 'Quantity is recommended to be a higher number. User gets reward very easily if quantity is low',
                    ],
                    'topup_status' => [
                        'type' => 'select',
                        'possible_value' => ['Successful' => 'Successful'],
                        'is_multi_selectable' => 0
                    ],
                    'operator' => [
                        'type' => 'select',
                        'possible_value' => $this->operator,
                        'is_multi_selectable' => 1
                    ],
                    'sim_type' => [
                        'type' => 'select',
                        'possible_value' => ['prepaid' => 'Prepaid', 'postpaid' => 'Postpaid'],
                        'is_multi_selectable' => 1
                    ]
                ]
            ],

            'wallet_recharge' => [
                'name' => 'Point Recharge',
                'event_class' => 'Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge\Event',
                'rule_class' => 'Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge\Rule',
                'parameters' => [
                    'target' => [
                        'type' => 'number',
                        'min' => 0
                    ],
                    'gateway' => [
                        'type' => 'select',
                        'possible_value' => ['all' => 'All', 'bkash' => 'bKash', 'nagad' => 'Nagad'],
                        'is_multi_selectable' => 1
                    ],
                    'recharge_status' => [
                        'type' => 'select',
                        'possible_value' => ['completed' => 'Successful'],
                        'is_multi_selectable' => 0,
                    ]
                ]
            ],
        ];
    }

    private function getAffiliateActions()
    {
        return [
            'wallet_recharge' => [
                'name' => 'Wallet Recharge',
                'event_class' => \Sheba\Reward\Event\Affiliate\Action\WalletRecharge\Event::class,
                'rule_class' => \Sheba\Reward\Event\Affiliate\Action\WalletRecharge\Rule::class,
                'parameters' => [
                    'amount' => [
                        'type' => 'number',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Affiliate\Action\WalletRecharge\Parameter\Amount::class
                    ]
                ]
            ],
            'top_up' => [
                'name' => 'Top Up',
                'event_class' => \Sheba\Reward\Event\Affiliate\Action\TopUp\Event::class,
                'rule_class' => \Sheba\Reward\Event\Affiliate\Action\TopUp\Rule::class,
                'parameters' => [
                    'amount_greater_than' => [
                        'type' => 'number',
                        'min' => 1,
                        'class' => \Sheba\Reward\Event\Affiliate\Action\TopUp\Parameter\AmountGreaterThan::class
                    ],
                    'fixed_amount' => [
                        'type' => 'number',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Affiliate\Action\TopUp\Parameter\FixedAmount::class
                    ],
                    'operator' => [
                        'type' => 'select',
                        'possible_value' => $this->operator,
                        'is_multi_selectable' => 1,
                        'class' => \Sheba\Reward\Event\Affiliate\Action\TopUp\Parameter\Operator::class
                    ],
                    'lifetime_topup_count' => [
                        'type' => 'number',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Affiliate\Action\TopUp\Parameter\LifetimeTopupCount::class
                    ],
                    'topup_day_count' => [
                        'type' => 'range',
                        'min' => 0,
                        'class' => \Sheba\Reward\Event\Affiliate\Action\TopUp\Parameter\TopupDayCount::class
                    ],
                    'no_topup_day_count' => [
                        'type' => 'number',
                        'min' => 1,
                        'class' => \Sheba\Reward\Event\Affiliate\Action\TopUp\Parameter\NoTopupDayCount::class
                    ]
                ]
            ]
        ];
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

    public function setOperators()
    {
        $operators = TopUpVendor::select('id', 'name')->get();
        // $this->operator['all'] = 'All';
        foreach ($operators as $operator){
            $this->operator[$operator->id] = $operator->name;
        }
    }
}

// 01. possible_value for parameters should be associative array (JS object)
