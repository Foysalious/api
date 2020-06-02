<?php namespace App\Transformers\Business;

use App\Models\Attachment;
use App\Models\Procurement;
use App\Transformers\AttachmentTransformer;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class TenderDetailsTransformer extends TransformerAbstract
{
    const ZERO = 0;
    const ONE = 1;
    const THRESHOLD = 5;
    /** @var bool $isForDetails */
    private $isForDetails;

    public function __construct($is_for_details = false)
    {
        $this->isForDetails = $is_for_details;
    }

    /**
     * @param Procurement $procurement
     * @return array
     */
    public function transform(Procurement $procurement)
    {
        $start_date = $procurement->procurement_start_date->format('d/m/y');
        $end_date = $procurement->procurement_end_date->format('d/m/y');
        $category = $procurement->category ? $procurement->category : null;
        $number_of_bids = $procurement->bids()->count();
        $number_of_participants = $procurement->number_of_participants;
        $data = [
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
            'remaining_days' => $this->getRemainingDaysWithIconAndColor($procurement),
            'number_of_participants' => $number_of_participants ?: null,
            'number_of_applicants_or_applications' => !$number_of_participants ?
                $this->getApplicants($number_of_bids) :
                $this->getRemainingApplications($number_of_participants, $number_of_bids),
            'created_at' => 'Posted ' . $procurement->created_at->diffForHumans()
        ];

        if ($this->isForDetails) $data += [
            'attachments' => $procurement->attachments->map(function (Attachment $attachment) {
                return (new AttachmentTransformer())->transform($attachment);
            })->toArray(),
            'is_invited_vendor_only' => $procurement->shared_to == config('b2b.SHARING_TO.own_listed.key'),
            'is_sheba_verified_vendor_only' => $procurement->shared_to == config('b2b.SHARING_TO.verified.key')
        ];

        return $data;
    }

    /**
     * @param Procurement $procurement
     * @return string[]|null
     */
    private function getRemainingDaysWithIconAndColor(Procurement $procurement)
    {
        $procurement_remaining_days = $procurement->getRemainingDays();

        if ($procurement_remaining_days == self::ZERO) return null;
        if ($procurement_remaining_days == self::ONE) return [
            'days' => $procurement->last_date_of_submission->diffInHours(Carbon::today()) . ' hours remaining',
            'color' => '#e75050',
            'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/stopwatch.png',
        ];
        return [
            'days' => $procurement_remaining_days . ' days remaining',
            'color' => '#f5b861',
            'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/stopwatch.png',
        ];
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
            'applications' => $remaining_application . ' applications remaining',
            'color' => '#e75050',
            'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/users.png',
        ];

        return [
            'applications' => $remaining_application . ' applications remaining',
            'color' => '#f5b861',
            'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/users.png',
        ];
    }
}
