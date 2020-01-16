<?php

namespace Sheba\DueTracker;

use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\BaseRepository;
use Sheba\FileManagers\CdnFileManager;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class DueTrackerRepository extends BaseRepository
{
    use ModificationFields,CdnFileManager;

    public function getDueList(Request $request, $paginate = true)
    {
        $url      = "accounts/$this->accountId/entries/due-list?";
        $url      = $this->updateRequestParam($request, $url);
        $order_by = $request->order_by;
        $result   = $this->client->get($url);
        /** @var Collection $list */
        $list = $this->attachProfile(collect($result['data']['list']));
        if ($request->has('balance_type') && in_array($request->balance_type, [
                'due',
                'received',
                'clear'
            ])) {
            $list = $list->where('balance_type', $request->balance_type)->values();
        }
        if (!empty($order_by) && $order_by == "name") {
            $order = $request->order == 'desc' ? 'sortBy' : 'sortByDesc';
            $list  = $list->$order('customer_name')->values();
        }
        if ($paginate) {
            list($offset, $limit) = calculatePagination($request);
            $list = $list->slice($offset)->take($limit);
        }
        return [
            'list'  => $list,
            'stats' => $result['data']['totals']
        ];
    }

    private function updateRequestParam(Request $request, $url)
    {
        if (!empty($order_by) && $order_by != "name") {
            $order = !empty($request->order) ? strtolower($request->order) : 'desc';
            $url   .= "&order_by=$order_by&order=$order";
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $url .= "&start=$request->start_date&end=$request->end_date";
        }
        return $url;
    }

    private function attachProfile(Collection $list)
    {
        $list = $list->map(function ($item) {
            /** @var Profile $profile */
            $profile                 = Profile::select('name', 'mobile', 'id', 'pro_pic')->find($item['profile_id']);
            $item['customer_name']   = $profile ? $profile->name : "Unknown";
            $item['customer_mobile'] = $profile ? $profile->mobile : null;
            $item['avatar']          = $profile ? $profile->pro_pic : null;
            $item['customer_id']     = $profile ? $profile->posCustomer ? $profile->posCustomer->id : null : null;
            return $item;
        });
        return $list;
    }

    /**
     * @param Partner $partner
     * @param Request $request
     * @return array
     * @throws InvalidPartnerPosCustomer
     * @throws ExpenseTrackingServerError
     */
    public function getDueListByProfile(Partner $partner, Request $request)
    {
        $partner_pos_customer = PartnerPosCustomer::byPartner($partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if (empty($partner_pos_customer))
            throw new InvalidPartnerPosCustomer();
        /** @var PosCustomer $customer */
        $customer = $partner_pos_customer->customer;
        $url      = "accounts/$this->accountId/entries/due-list/$customer->profile_id?";
        $url      = $this->updateRequestParam($request, $url);
        $result   = $this->client->get($url);
        $list     = collect($result['data']['list']);
        list($offset, $limit) = calculatePagination($request);
        $list = $list->slice($offset)->take($limit);
        return [
            'list'     => $list,
            'stats'    => $result['data']['totals'],
            'customer' => [
                'id'                => $customer->id,
                'name'              => $customer->profile->name,
                'mobile'            => $customer->profile->mobile,
                'avatar'            => $customer->profile->pro_pic,
                'due_date_reminder' => $partner_pos_customer->due_date_reminder
            ]
        ];
    }

    /**
     * @param Partner $partner
     * @param Request $request
     * @throws InvalidPartnerPosCustomer
     */
    public function store(Partner $partner, Request $request)
    {

        $partner_pos_customer = PartnerPosCustomer::byPartner($partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if (empty($partner_pos_customer))
            throw new InvalidPartnerPosCustomer();
        /** @var PosCustomer $customer */
        $customer = $partner_pos_customer->customer;
        $this->setModifier($partner);
        $data = $this->createStoreData($request);
    }

    private function createStoreData(Request $request)
    {
        $data                   = $request->except('manager_resource', 'partner', 'customer_id');
        $data['created_from']   = $this->withBothModificationFields((new RequestIdentification())->get());
        $data['amount_cleared'] = $request->type == "due" ? 0 : $request['amount'];
        $data['head_name']      = AutomaticIncomes::DUE_TRACKER;
        $data['created_at']     = $request->created_at;
        $data['attachments']    = $this->uploadAttachments($request);
    }

    private function uploadAttachments(Request $request)
    {
        if ($request->has('attachments')) {
            foreach ($request->allFiles() as $file) {
                
            }
        }
    }

}
