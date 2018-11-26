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
            'amount' => (double)$offer->amount,
            'amount_text' => $this->getAmountText($offer, $target_type),
            'start_date' => $offer->start_date,
            'end_date' => $offer->end_date,
            'is_applied' => $is_applied,
            'target_id' => (int)$offer->target_id,
            'code' => $target_type == 'voucher' ? $offer->voucher ? $offer->voucher->code : null : null,
            'voucher_title' => $target_type == 'voucher' ? $offer->voucher ? $offer->voucher->title : null : null,
            'is_amount_percent' => $this->isAmountPercent($target_type, $offer),
            'has_cap' => $this->getCapStatus($offer, $target_type)
        ];
    }

    private function getAmountText($offer, $target_type)
    {
        switch ($target_type) {
            case 'voucher':
                $data = 'Save ';
                if ($offer->voucher->cap > 0) {
                    $data .= 'Upto ';
                }
                return $data;
            case 'reward':
                $data = 'Save ';
                if ($offer->reward->cap > 0) {
                    $data .= 'Upto ';
                }
                return $data;
            default:
                return 'Price ';

        }
    }

    private function getCapStatus($offer, $target_type)
    {
        switch ($target_type) {
            case 'voucher':
                return $offer->voucher->cap > 0;
            case 'reward':
                return $offer->reward->cap > 0;
            default:
                return false;

        }
    }

    private function isAmountPercent($target_type, $offer)
    {
        switch ($target_type) {
            case 'voucher':
                if ($offer->voucher->is_amount_percentage > 0) {
                    return true;
                } else {
                    return false;
                }
            case 'reward':
                if ($offer->reward->is_amount_percentage) {
                    return true;
                } else {
                    return false;
                }
            default:
                return false;

        }
    }
}