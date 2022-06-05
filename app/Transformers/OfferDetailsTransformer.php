<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 11/25/2018
 * Time: 5:31 PM
 */

namespace App\Transformers;

use Sheba\Dal\Category\Category;
use App\Models\Promotion;
use Sheba\Dal\Service\Service;
use League\Fractal\TransformerAbstract;
use Throwable;

class OfferDetailsTransformer extends TransformerAbstract
{
    public function transform($offer)
    {

        $target_type = strtolower(snake_case(str_replace("App\\Models\\", '', $offer->target_type)));
        $is_applied = isset($offer->customer_id) && $target_type == 'voucher' ? !!Promotion::isApplied($offer->customer_id, $offer->target_id)->count() : false;
        $category_id = $this->getCategoryId($offer, $target_type);
        $category = $this->getCategory($category_id);
        return [
            'id' => $offer->id,
            'thumb' => $offer->thumb,
            'banner' => $offer->app_banner,
            'original_banner' => $offer->banner,
            'title' => $offer->title,
            'structured_title' => $offer->structured_title,
            'short_description' => $offer->short_description,
            'target_link' => $offer->target_link,
            'detail_description' => $offer->detail_description,
            'structured_description' => $offer->structured_description,
            'target_type' => $target_type,
            'amount' => (double)$offer->amount,
            'amount_text' => $this->getAmountText($offer, $target_type),
            'start_date' => $offer->start_date->toDateTimeString(),
            'end_date' => $offer->end_date->toDateTimeString(),
            'is_applied' => $is_applied,
            'target_id' => (int)$offer->target_id,
            'code' => $target_type == 'voucher' ? $offer->voucher ? $offer->voucher->code : null : null,
            'voucher_title' => $target_type == 'voucher' ? $offer->voucher ? $offer->voucher->title : null : null,
            'is_amount_percent' => $this->isAmountPercent($offer, $target_type),
            'has_cap' => $this->getCapStatus($offer, $target_type),
            'category_id' => !is_null($category_id) ? (string)$category_id : null,
            'category_slug' => $category ? $category->slug : null,
            'slug' => $offer->isCategory() || $offer->isService() ? $offer->target->getSlug() : null,
            'is_category_master' => $category ? !$category->parent_id : false,
            'service_slug' => $target_type == 'service' ? $this->getServiceSlug($offer->target_id) : null,
            'button_text' => $offer->button_text,
        ];
    }

    private function getAmountText($offer, $target_type)
    {
        switch ($target_type) {
            case 'voucher':
            case 'reward':
                $data = 'Save ';
                if ((double)$offer->target->cap > 0) {
                    $data .= 'Upto ';
                }
                return $data;
            default:
                return 'Save Upto ';

        }
    }

    private function getCapStatus($offer, $target_type)
    {
        switch ($target_type) {
            case 'voucher':
            case 'reward':
                return !!$offer->target->cap;
            default:
                return false;

        }
    }

    private function isAmountPercent($offer, $target_type)
    {
        switch ($target_type) {
            case 'voucher':
            case 'reward':
                return !!$offer->target->is_amount_percentage;
            default:
                return false;

        }
    }

    public function getCategoryId($offer, $target_type)
    {
        switch ($target_type) {
            case 'voucher':
                $rules = json_decode($offer->target->rules);
                $count = isset($rules->categories) && is_array($rules->categories) ? count($rules->categories) : 0;
                if ($count == 1) return (int)$rules->categories[0];
                elseif ($count == 2) return $this->isVoucherCategoriesRentACar($rules->categories) ? (int)$rules->categories[0] : null;
                else return null;
            case 'reward':
                if ($offer->target->categoryNoConstraints && $offer->target->categoryNoConstraints->count() > 0) {
                    return null;
                } elseif ($offer->target->categoryConstraints && $offer->target->categoryConstraints->count() == 1) {
                    return $offer->target->categoryConstraints->first()->constraint_id;
                } else {
                    return null;
                }
            case 'category':
                return (int)$offer->target_id;
            default:
                return null;
        }
    }

    private function isVoucherCategoriesRentACar($voucher_categories)
    {
        $rent_a_car_categories = array_map('intval', explode(',', env('RENT_CAR_IDS')));
        foreach ($voucher_categories as $voucher_category) {
            if (!in_array($voucher_category, $rent_a_car_categories)) return false;
        }
        return true;
    }

    private function getCategory($category_id)
    {
        try {
            if ($category_id) {
                return Category::find($category_id);
            } else {
                return null;
            }
        } catch (Throwable $e) {
            return null;
        }
    }

    private function getServiceSlug($service_id)
    {
        try {
            if ($service_id) {
                return Service::find($service_id)->slug;
            } else {
                return null;
            }
        } catch (Throwable $e) {
            return null;
        }
    }
}