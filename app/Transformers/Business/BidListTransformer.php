<?php namespace App\Transformers\Business;

use App\Models\Procurement;
use League\Fractal\TransformerAbstract;

class BidListTransformer extends TransformerAbstract
{
    const VERIFIED = 'Verified';
    const INVITED = 'Invited';
    const PUBLISHED = 'Published';

    private $procurement;

    public function __construct(Procurement $procurement)
    {
        $this->procurement = $procurement;
    }

    public function transform($bid)
    {
        $bidder = $bid->bidder;
        return [
            'id' => $bid->id,
            'service_provider' => [
                'id' => $bidder->id,
                'name' => $bidder->name,
                'image' => $bid->bidder->logo,
                'status' => $this->getStatus($bidder),
                'rating' => number_format($bidder->reviews()->avg('rating'), 1),
            ],
            'price' => $bid->price,
        ];
    }

    private function getStatus($bidder)
    {
        $invitations_lists = $this->procurement->invitations->pluck('partner_id')->toArray();
        if (in_array($bidder->id, $invitations_lists)) return self::INVITED;
        if ($bidder->status == self::VERIFIED) return self::VERIFIED;
        return self::PUBLISHED;
    }
}