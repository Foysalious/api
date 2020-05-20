<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use App\Models\Category;
use Carbon\Carbon;

class TenderTransformer extends TransformerAbstract
{
    const ZERO = 0;
    const ONE = 1;
    const THRESHOLD = 5;

    /**
     * @param $procurement
     * @return array
     */
    public function transform($procurement)
    {
        $start_date = $procurement->procurement_start_date->format('d/m/y');
        $end_date = $procurement->procurement_end_date->format('d/m/y');
        $category = $procurement->category_id ? Category::findOrFail($procurement->category_id) : null;
        $number_of_bids = $procurement->bids()->count();
        $number_of_participants = $procurement->number_of_participants;
        return [
            'id' => $procurement->id,
            'title' => $procurement->title,
            'description' => $procurement->long_description,
            'labels' => $procurement->getTagNamesAttribute()->toArray(),
            'last_date_of_submission' => [
                'date' => $procurement->last_date_of_submission->format('d/m/Y'),
                'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/deadline.png',
            ],
            'delivery_within' => [
                'date_range' => $start_date . ' - ' . $end_date,
                'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/time.png',
            ],
            'estimated_price' => $procurement->estimated_price ? (double)$procurement->estimated_price : null,

            'payment' => $procurement->payment_options ? [
                'option' => $procurement->payment_options,
                'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/bdt.png',
            ] : null,

            'type' => $procurement->type,
            'shared_to' => $procurement->shared_to,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/category.png',
            ] : null,

            'remaining_days' => $this->getRemainingDays($procurement->last_date_of_submission),
            'number_of_applicants_or_applications' => !$number_of_participants ? $this->getApplicants($number_of_bids) :
                $this->getRemainingApplications($number_of_participants, $number_of_bids),
            'created_at' => 'Posted ' . $procurement->created_at->diffForHumans(),
        ];
    }

    /**
     * @param $last_date_of_submission
     * @return array|null
     */
    private function getRemainingDays($last_date_of_submission)
    {
        $today = Carbon::now();
        if ($last_date_of_submission->greaterThanOrEqualTo($today)) {
            $total_days = $last_date_of_submission->diffInDays($today) + 1;
            if ($total_days == self::ONE) return [
                'days' => $last_date_of_submission->diffInHours($today) . ' hours remaining',
                'color' => '#e75050',
                'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/stopwatch.png',
            ];
            return [
                'days' => $total_days . ' days remaining',
                'color' => '#f5b861',
                'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/stopwatch.png',
            ];
        }
        return null;
    }

    /**
     * @param $bids
     * @return array|null
     */
    public function getApplicants($bids)
    {
        if ($bids == self::ZERO) return null;
        return [
            'vendors' => $bids . ' vendors applied so far',
            'color' => '#38c8e7',
            'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/users.png',
        ];
    }

    /**
     * @param $number_of_participants
     * @param $number_of_bids
     * @return array|null
     */
    private function getRemainingApplications($number_of_participants, $number_of_bids)
    {
        if (!$number_of_bids) return null;
        $remaining_application = $number_of_participants - $number_of_bids;
        if ($remaining_application < self::THRESHOLD) return [
            'applications' => $number_of_participants - $number_of_bids . ' applications remaining',
            'color' => '#e75050',
            'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/users.png',
        ];
        return [
            'applications' => $number_of_participants - $number_of_bids . ' applications remaining',
            'color' => '#f5b861',
            'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/users.png',
        ];

    }
}