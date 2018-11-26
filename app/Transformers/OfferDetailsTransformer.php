<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 11/25/2018
 * Time: 5:31 PM
 */

namespace App\Transformers;

use App\Models\Promotion;
use League\Fractal\TransformerAbstract;

class OfferDetailsTransformer extends TransformerAbstract
{
    public function transform($offer)
    {

        $target_type = strtolower(snake_case(str_replace("App\\Models\\", '', $offer->target_type)));
        $is_applied = isset($offer->customer_id) && $target_type == 'voucher' ? !!Promotion::isApplied($offer->customer_id, $offer->target_id)->count() : false;
        return [
            'id' => $offer->id,
            'thumb' => $offer->thumb,
            'banner' => $offer->banner,
            'title' => $offer->title,
            'structured_title' => $offer->structured_title,
            'short_description' => $offer->short_description,
            'target_link' => $offer->target_link,
            'structured_description' => $offer->structured_description,
            'target_type' => $target_type,
            'amount' => 200,
            'amount_text' => $this->getAmountText($offer, $target_type),
            'start_date' => $offer->start_date,
            'end_date' => $offer->end_date,
            'is_applied' => $is_applied,
            'target_id' => (int)$offer->target_id,
            'code' => $target_type == 'voucher' ? $offer->voucher ? $offer->voucher->code : null : null,
            'voucher_title' => $target_type == 'voucher' ? $offer->voucher ? $offer->voucher->title : null : null
        ];
    }

    private function getAmountText($offer, $target_type)
    {
        switch ($target_type) {
            case 'voucher':
                $data = 'Save ';
                if ($offer->voucher->cap > 0) {
                    $data .= ('Up to ৳ ' . $offer->voucher->cap);
                } else if ($offer->voucher->is_amount_percentage > 0) {
                    $data .= $offer->amount . ' %';
                } else {
                    $data .= '৳ ';
                    $data .= ($offer->ammount) ? $offer->amount : $offer->voucher->amount;
                }
                return $data;
            case 'reward':
                $data = 'Save ';
                if ($offer->reward->cap > 0) {
                    $data .= 'Up to ৳ ' . $offer->reward->cap;
                } else if ($offer->reward->is_amount_percentage) {
                    $data .= $offer->amount . ' %';
                } else {
                    $data .= '৳ ';
                    $data .= $offer->ammount ? $offer->amount : $offer->reward->amount;
                }
                return $data;
            default:
                return 'Price ৳ ' . $offer->amount;

        }
    }
}