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
        DB::transaction(function () use ($user, $product) {
            $order_create_data = [
                'reward_product_id' => $product->id,
                'order_creator_type' => get_class($user),
                'order_creator_id' => $user->id,
                'reward_product_point' => $product->point
            ];
            RewardShopOrder::create($this->withCreateModificationField($order_create_data));

            $user->decrement('reward_point', $product->point);
            (new RewardPointLogRepository())->storeOutLog($user, $product->point, "$product->point Point Deducted for $product->name Purchase");
        });

        $user_name = '';
        if (get_class($user) == constants('REWARD_TARGET_TYPE')['Partner']) $user_name = $user->name;
        elseif (get_class($user) == constants('REWARD_TARGET_TYPE')['Customer']) $user_name = $user->profile->name;

        $this->notifyPM("$user_name has placed a reward order.", env('SHEBA_BACKEND_URL'). '/reward-shop');

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
    protected function notifyPM($title = 'New Reward Order Placed', $link = null)
    {
        $link = $link ? : env('SHEBA_BACKEND_URL');

        notify()->department(Department::where('name', 'PM')->first())->send([
            "title" => $title,
            "link" => $link,
            "type" => notificationType('Info')
        ]);
    }
}