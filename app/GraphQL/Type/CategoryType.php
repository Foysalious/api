<?php

namespace App\GraphQL\Type;

use App\Models\ScheduleSlot;
use Carbon\Carbon;
use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
use Redis;

class CategoryType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Category',
        'description' => 'Sheba Category'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()],
            'short_description' => ['type' => Type::string()],
            'long_description' => ['type' => Type::string()],
            'meta_title' => ['type' => Type::string()],
            'meta_description' => ['type' => Type::string()],
            'slug' => ['type' => Type::string()],
            'thumb' => ['type' => Type::string()],
            'banner' => ['type' => Type::string()],
            'app_thumb' => ['type' => Type::string()],
            'app_banner' => ['type' => Type::string()],
            'publication_status' => ['type' => Type::int()],
            'icon' => ['type' => Type::string()],
            'icon_png' => ['type' => Type::string()],
            'questions' => ['type' => Type::int()],
            'children' => [
                'type' => Type::listOf(GraphQL::type('Category'))
            ],
            'parent' => [
                'type' => GraphQL::type('Category')
            ],
            'reviews' => [
                'args' => [
                    'rating' => ['type' => Type::listOf(Type::int())],
                    'isEmptyReview' => ['type' => Type::boolean()]
                ],
                'type' => Type::listOf(GraphQL::type('Review'))
            ],
            'services' => [
                'args' => [
                    'id' => ['type' => Type::listOf(Type::int())],
                ],
                'type' => Type::listOf(GraphQL::type('Service'))
            ],
            'usps' => ['type' => Type::listOf(GraphQL::type('Usp'))],
            'total_partners' => [
                'args' => ['location_id' => ['type' => Type::int()]],
                'type' => Type::int(), 'description' => 'Total partner count of Category'],
            'total_available_partners' => [
                'args' => ['location_id' => ['type' => Type::int()]],
                'type' => Type::int(),
                'description' => 'Total partner count of Category'],
            'total_services' => ['type' => Type::int(), 'description' => 'Total service count of Category'],
            'total_jobs' => ['type' => Type::int(), 'description' => 'Total served jobs of Category'],
            'total_experts' => ['type' => Type::int(), 'description' => 'Total expert count of Category'],
            'total_good_reviews' => ['type' => Type::int(), 'description' => 'Total good reviews of Category'],
            'updated_at_timestamp' => ['type' => Type::int(), 'description' => 'Timestamp when any of the row information has been last updated']
        ];
    }

    protected function resolveServicesField($root, $args)
    {
        $root->load(['services' => function ($q) use ($args) {
            $q->published()->orderBy('order');
            if (isset($args['id'])) {
                $q->whereIn('id', $args['id']);
            }
        }]);
        return $root->services ? $root->services : null;
    }

    protected function resolveChildrenField($root, $args)
    {
        if ($root->isParent()) {
            return $root->children->sortBy('order');
        } else {
            return null;
        }
    }

    protected function resolveParentField($root, $args)
    {
        return $root->parent;
    }

    protected function resolveReviewsField($root, $args)
    {
        $root->load(['reviews' => function ($q) use ($args) {
            if (isset($args['rating'])) {
                $q->whereIn('rating', $args['rating']);
            }
            $q->with('customer.profile', 'partner', 'rates');
        }]);
        $final = collect();
        $reviews = $root->reviews;
        foreach ($reviews as &$review) {
            if (count($review->rates) > 0) {
                foreach ($review->rates as $rate) {
                    if (!empty($rate->rate_answer_text)) {
                        $review->review = $rate->rate_answer_text;
                        break;
                    }
                }
            }
            if (!empty($review->review)) {
                $final->push($review);
            }
        }
        $root->reviews = $final->sortByDesc('id');
        return $root->reviews;
    }

    protected function resolveTotalPartnersField($root, $args)
    {
        $root->load(['partners' => function ($q) use ($args) {
            $q->verified()->where('category_partner.is_verified', 1);
            if (isset($args['location_id'])) {
                if ($args['location_id']) {
                    $q->whereHas('locations', function ($q) use ($args) {
                        $q->where('locations.id', (int)$args['location_id']);
                    });
                }
            }
        }]);
        return $root->partners->count();
    }

    protected function resolveTotalAvailablePartnersField($root, $args)
    {
        if (!isset($args['location_id'])) {
            return null;
        }
        $root->load(['partners' => function ($q) use ($args, $root) {
            $q->verified()->with('handymanResources')->where('category_partner.is_verified', 1)->whereHas('locations', function ($query) use ($args) {
                $query->where('locations.id', (int)$args['location_id']);
            });
        }]);
        $first = $this->getFirstValidSlot();
        foreach ($root->partners as $partner) {
            if (!((scheduler($partner)->isAvailable((Carbon::today())->format('Y-m-d'), explode('-', $first)[0], $root->id)))->get('is_available')) {
                unset($partner);
            }
        }
        return $root->partners->count();
    }

    protected function resolveTotalExpertsField($root, $args)
    {
        $root->load(['partnerResources' => function ($q) {
            $q->whereHas('resource', function ($query) {
                $query->verified();
            });
        }]);
        return $root->partnerResources->count();
    }

    protected function resolveTotalServicesField($root, $args)
    {
        $root->load(['services' => function ($q) {
            $q->published();
        }]);
        return $root->services->count();
    }

    protected function resolveUpdatedAtTimestampField($root, $args)
    {
        return $root->updated_at->timestamp;
    }

    protected function resolveTotalJobsField($root, $args)
    {
        $root->load(['jobs' => function ($q) {
            $q->where('status', 'Served');
        }]);
        return $root->jobs->count();
    }

    protected function resolveTotalGoodReviewsField($root, $args)
    {
        $root->load(['reviews' => function ($q) {
            $q->whereIn('rating', [4, 5]);
        }]);
        return $root->reviews->count();
    }

    protected function resolveUspsField($root, $args)
    {
        $root->load('usps');
        return $root->usps;
    }

    private function getFirstValidSlot()
    {
        $slots = ScheduleSlot::all();
        $current_time = Carbon::now();
        foreach ($slots as $slot) {
            $slot_start_time = Carbon::parse($slot->start);
            $time_slot_key = $slot->start . '-' . $slot->end;
            if ($slot_start_time > $current_time) {
                return $time_slot_key;
            }
        }
    }
}