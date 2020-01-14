<?php

namespace Sheba\DueTracker;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\ExpenseTracker\Repository\BaseRepository;

class DueTrackerRepository extends BaseRepository
{
    public function getDueList(Request $request)
    {
        $url      = "accounts/$this->accountId/entries/due-list?";
        $order_by = $request->order_by;
        if (!empty($order_by) && $order_by != "name") {
            $order = !empty($request->order) ? strtolower($request->order) : 'desc';
            $url   .= "&order_by=$order_by&order=$order";
        }
        list($offset, $limit) = calculatePagination($request);
        $result = $this->client->get($url);
        /** @var Collection $list */
        $list = $this->attachProfile(collect($result['data']['list']));
        if ($request->has('balance_type') && in_array($request->balance_type, [
                'due',
                'received',
                'clear'
            ])) {
            $list = $list->where('balance_type', $request->balance_type)->values();
        }
        if (!empty($request->order_by) && $request->order_by == "name") {
            $order = $request->order == 'desc' ? 'sortBy' : 'sortByDesc';
            $list  = $list->$order('customer_name')->values();
        }
        $list = $list->slice($offset)->take($limit);
        return [
            'list'  => $list,
            'stats' => $result['data']['totals']
        ];
    }

    private function attachProfile(Collection $list)
    {
        $list = $list->map(function ($item) {
            $profile                 = Profile::select('name', 'mobile', 'id', 'pro_pic')->find($item['profile_id']);
            $item['customer_name']   = $profile ? $profile->name : "Unknown";
            $item['customer_mobile'] = $profile ? $profile->mobile : null;
            $item['avatar']          = $profile ? $profile->pro_pic : null;
            return $item;
        });
        return $list;
    }

}
