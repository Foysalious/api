<?php namespace App\Transformers\Business;

use App\Models\Category;
use App\Models\Procurement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class TenderTransformer extends TransformerAbstract
{

    public function transform($procurement)
    {
        $start_date = Carbon::parse($procurement->procurement_start_date)->format('d/m/y');
        $end_date = Carbon::parse($procurement->procurement_end_date)->format('d/m/y');
        $category = $procurement->category_id ? Category::findOrFail($procurement->category_id) : null;
        $number_of_bids = $procurement->bids()->where('status', '<>', 'pending')->count();
        return [
            'id' => $procurement->id,
            'title' => $procurement->title,
            'description' => $procurement->long_description,
            'labels' => $procurement->getTagNamesAttribute()->toArray(),
            'last_date_of_submission' => [
                'date' => Carbon::parse($procurement->last_date_of_submission)->format('d/m/Y'),
                'icon' => 'icon',
            ],
            'delivery_within' => [
                'date_range' => $start_date . ' - ' . $end_date,
                'icon' => 'icon'
            ],
            'estimated_price' => $procurement->estimated_price ? (double)$procurement->estimated_price : 'Budget to be fixed',

            'is_payment_option_available' => $procurement->payment_options ? 1 : 0,
            'payment' => $procurement->payment_options ? [
                'option' => $procurement->payment_options,
                'icon' => 'icon'
            ] : [],

            'type' => $procurement->type,
            'shared_to' => $procurement->shared_to,
            'is_category_available' => $category ? 1 : 0,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => 'icon'
            ] : [],

            'number_of_participants' => $procurement->number_of_participants,
            'remaining_days' => $this->getRemainingDays($procurement->last_date_of_submission),
            'is_remaining_applications_available' => $number_of_bids ? 1 : 0,
            'remaining_applications' => $number_of_bids ? $this->getRemainingApplications($procurement, $number_of_bids) : [],
            'created_at' => 'Posted ' . Carbon::parse($procurement->created_at)->diffForHumans(),
        ];
    }

    private function getRemainingApplications($procurement, $number_of_bids)
    {
        $number_of_participants = $procurement->number_of_participants;
        if ($number_of_participants) {
            if ($procurement->shared_to == 'verified') return [
                'applications' => $number_of_participants - $number_of_bids . ' applications remaining',
                'color' => 'Red',
                'icon' => 'icon'
            ];
            return [
                'applications' => $number_of_participants - $number_of_bids . ' applications remaining',
                'color' => 'Yellow',
                'icon' => 'icon'
            ];
        }
        return [
            'applications' => $number_of_bids . ' vendors applied so far',
            'color' => 'Blue',
            'icon' => 'icon'
        ];
    }

    private function getRemainingDays($last_date_of_submission)
    {
        $today = Carbon::now();
        $last_date_of_submission = Carbon::parse($last_date_of_submission);
        if ($last_date_of_submission->greaterThanOrEqualTo($today)) {
            $total_days = $last_date_of_submission->diffInDays($today) + 1;
            if ($total_days == 1) return [
                'days' => $last_date_of_submission->diffInHours($today) . ' hours remaining',
                'color' => 'Red',
                'icon' => 'icon'
            ];
            return [
                'days' => $total_days . ' days remaining',
                'color' => 'Yellow',
                'icon' => 'icon'
            ];
        }
        return [];
    }
}