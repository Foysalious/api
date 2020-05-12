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
            'days_remain' => [
                'days' => Carbon::parse($procurement->last_date_of_submission)->diffInDays(Carbon::now()),
                'color' => 'Red',
                'icon' => 'icon'
            ],
            'created_at' => 'Posted ' . Carbon::parse($procurement->created_at)->diffForHumans(),
        ];
    }
}