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
                            'target' => [
                                'type'  => 'number',
                                'min'   => 0
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
                'action'    => [],
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