<?php namespace Sheba\RewardShop;

use App\Models\Department;
use App\Models\RewardShopOrder;
use App\Models\RewardShopProduct;
use Illuminate\Support\Facades\DB;
use Sheba\ModificationFields;
use Sheba\Repositories\RewardPointLogRepository;

class OrderHandler
{
    use ModificationFields;

    /**
     * @param RewardShopProduct $product
     * @param $user
     * @throws \Exception
     */
    public function create(RewardShopProduct $product, $user)
    {
        $order = null;

        DB::transaction(function () use ($user, $product, &$order) {
            $order_create_data = [
                'reward_product_id' => $product->id,
                'order_creator_type' => get_class($user),
                'order_creator_id' => $user->id,
                'reward_product_point' => $product->point
            ];
            $order = RewardShopOrder::create($this->withCreateModificationField($order_create_data));

            $user->decrement('reward_point', $product->point);
            (new RewardPointLogRepository())->storeOutLog($user, $product->point, "$product->point Point Deducted for $product->name Purchase");
        });

        $user_name = '';
        if (get_class($user) == constants('REWARD_TARGET_TYPE')['Partner']) $user_name = $user->name;
        elseif (get_class($user) == constants('REWARD_TARGET_TYPE')['Customer']) $user_name = $user->profile->name;

        $this->notify("$user_name has placed a reward order.", config('sheba.admin_url'). '/reward-shop/order', $order);

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

    /**
     * @param string $title
     * @param null $link
     * @throws \Exception
     */
    protected function notify($title = 'New Reward Order Placed', $link = null, $order)
    {
        $link = $link ? : config('sheba.admin_url');

        notify()->departments([5, 9, 13, 18])->send([
            "title" => $title,
            "link"  => $link,
            "type"  => notificationType('Info'),
            "event_type"    => "App\\Models\\RewardShopOrder",
            "event_id"      => $order->id
        ]);
    }
}
