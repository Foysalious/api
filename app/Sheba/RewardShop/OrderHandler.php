<?php namespace Sheba\RewardShop;

use App\Models\RewardShopOrder;
use App\Models\RewardShopProduct;
use Illuminate\Support\Facades\DB;
use Sheba\ModificationFields;
use Sheba\Repositories\RewardLogRepository;
use Sheba\Repositories\RewardPointLogRepository;

class OrderHandler
{
    use ModificationFields;

    public function create(RewardShopProduct $product, $user)
    {
        DB::transaction(function () use ($user, $product) {
            $order_create_data = [
                'reward_product_id' => $product->id,
                'order_creator_type' => get_class($user),
                'order_creator_id' => $user->id,
                'reward_product_point' => $product->point
            ];
            RewardShopOrder::create($this->withCreateModificationField($order_create_data));

            $user->decrement('reward_point', $product->point);
            (new RewardPointLogRepository())->storeOutLog();
        });
    }

    public function statusChange(RewardShopOrder $order, $status)
    {
        try {
            $validator = (new OrderValidator())->setOrder($order);
            if ($validator->canChangeStatus($status)) {
                $order->update(['status' => $status]);
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}