<?php

namespace App\Sheba\AccountingEntry\Repository;

use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Models\Profile;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\RequestIdentification;

class AccountingDueTrackerRepository extends BaseRepository
{
    private $entry_id, $partner;

    /**
     * @param $entry_id
     * @return $this
     */
    public function setEntryId($entry_id)
    {
        $this->entry_id = $entry_id;
        return $this;
    }

    /**
     * @param $partner
     * @return $this
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function storeEntry(Request $request, $type, $with_update = false)
    {
        $this->getCustomer($request);
        $this->setModifier($request->partner);
        $data = $this->createEntryData($request, $type);
        $url = $with_update ? "api/entries/" . $request->entry_id : "api/entries/";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }


    public function deleteCustomer($customerId)
    {
        $url = "api/due-list/" . $customerId;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->delete($url);
    }

    /**
     * @param $request
     * @param false $paginate
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getDueList($request, $paginate = false): array
    {
//        try {
            $url = "api/due-list?";
            $url = $this->updateRequestParam($request, $url);
            $customerProfiles = null;
            if($request->has('q') && !empty($request->q)) {
                $profiles = PartnerPosCustomer::with([
                 'customer' => function($q) {
                     $q->select('id', 'profile_id');
                 },
                 'customer.profile' => function($q) {
                     $q->select('name', 'mobile', 'id', 'pro_pic');
                 }])->where('partner_id', $this->partner->id);

                if (is_numeric($request->q)) {
                    $profiles->whereHas('customer.profile', function ($query) use ($request) {
                        $query->where('mobile', 'like', '%'.$request->q.'%');
                    });
                }
                else {
                    $profiles->whereHas('customer.profile', function ($query) use ($request) {
                        $query->where('name', 'like', '%'.$request->q.'%');
                    });
                }
                $customerProfiles = $profiles->get();
                if ($customerProfiles->isEmpty()) {
                    return ['list' => []];
                }
                $ids = $profiles->get()->pluck('customer_id');
                $ids = implode(",", $ids->toArray());
                $url .= "&q=$ids";
            }
            $result = $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
            if ($customerProfiles) {
                $list = $this->attachCustomerProfile(collect($result['list']), $customerProfiles);
            } else {
                $list = $this->attachProfile(collect($result['list']));
            }
            $list = $list->reject(function ($value) {
                return $value == null;
            });

            if ($request->has('filter_by_supplier') && $request->filter_by_supplier == 1) {
                $list = $list->where('is_supplier', 1)->values();
            }
            if (!empty($order_by) && $order_by == "name") {
                $order = ($request->order == 'desc') ? 'sortByDesc' : 'sortBy';
                $list = $list->$order('customer_name', SORT_NATURAL | SORT_FLAG_CASE)->values();
            }
            $new_data = array();
            foreach ($list as $l)
                $new_data[] = $l;
            return [
                'list' => $new_data
            ];
//        } catch (AccountingEntryServerError $e) {
//            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
//        }
    }

    public function getDuelistBalance($request)
    {
        try {
            $url = "api/due-list/balance";
            $result = $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
            return [
                'total_transactions' => $result['total_transactions'],
                'total' => $result['total'],
                'stats' => $result['stats'],
                'partner' => $this->getPartnerInfo($request->partner),
            ];
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $request
     * @param $customerId
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getDueListByCustomer($request, $customerId): array
    {
        try {
            $url = "api/due-list/" . $customerId . "?";
            $url = $this->updateRequestParam($request, $url);
            $result = $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
            $due_list = collect($result['list']);

            $list = $due_list->map(
                function ($item) {
                    if ($item["attachments"]) {
                        $item["attachments"] = json_decode($item["attachments"]);
                    }
                    $item['created_at'] = Carbon::parse($item['created_at'])->format('Y-m-d h:i A');
                    $item['entry_at'] = Carbon::parse($item['entry_at'])->format('Y-m-d h:i A');
                    $pos_order = PosOrder::withTrashed()->find($item['source_id']);
                    $item['partner_wise_order_id'] = $item['source_type'] === 'POS' && $pos_order ? $pos_order->partner_wise_order_id : null;
                    return $item;
                }
            );
            return [
                'list' => $list
            ];
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function dueListBalanceByCustomer($request, $customerId)
    {
        try {
            $partner_pos_customer = PartnerPosCustomer::byPartner($this->partner->id)->where(
                'customer_id',
                $customerId
            )->with(['customer'])->first();
            $customer = PosCustomer::find($customerId);
            if (!empty($partner_pos_customer)) {
                $customer = $partner_pos_customer->customer;
            }
            if (empty($customer)) {
                throw new InvalidPartnerPosCustomer();
            }
            $url = "api/due-list/" . $customerId . "/balance";
            $result = $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
            $total_debit = $request['other_info']['total_debit'];
            $total_credit = $request['other_info']['total_credit'];
            $result['balance']['color'] = $total_debit > $total_credit ? '#219653' : '#DC1E1E';
            return [
                'customer' => [
                    'id' => $customer->id,
                    'name' => !empty($partner_pos_customer) && $partner_pos_customer->nick_name ? $partner_pos_customer->nick_name : $customer->profile->name,
                    'mobile' => $customer->profile->mobile,
                    'avatar' => $customer->profile->pro_pic,
                    'due_date_reminder' => !empty($partner_pos_customer) ? $partner_pos_customer->due_date_reminder : null,
                    'is_supplier' => !empty($partner_pos_customer) ? $partner_pos_customer->is_supplier : 0
                ],
                'partner' => $this->getPartnerInfo($this->partner),
                'stats' => $result['stats'],
                'other_info' => $result['other_info'],
                'balance' => $result['balance']
            ];
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Collection $list
     * @return Collection
     */
    private function attachProfile(Collection $list): Collection
    {
        $list = $list->map(
            function ($item) {
                $customerId = $item['party_id'];
                /** @var PosCustomer $posCustomer */
                $posCustomer = PosCustomer::find($customerId);
                if ($posCustomer) {
                    $profile_id = $posCustomer->profile_id;
                    /** @var Profile $profile */
                    $profile = Profile::select('name', 'mobile', 'id', 'pro_pic')->find($profile_id);
                    $customerId = $profile && isset($profile->posCustomer) ? $profile->posCustomer->id : null;
                    if (isset($customerId)) {
                        $posProfile = PartnerPosCustomer::byPartner($this->partner->id)->where(
                            'customer_id',
                            $customerId
                        )->first();
                    }
                    if (isset($posProfile) && isset($posProfile->nick_name)) {
                        $item['customer_name'] = $posProfile->nick_name;
                    } else {
                        $item['customer_name'] = $profile ? $profile->name : "Unknown";
                    }
                    $item['customer_mobile'] = $profile ? $profile->mobile : null;
                    $item['avatar'] = $profile ? $profile->pro_pic : null;
                    $item['customer_id'] = $customerId;
                    $item['is_supplier'] = isset($posProfile) ? $posProfile->is_supplier : 0;
                    return $item;
                }
                return false;
            }
        );
        return $list;
    }

    /**
     * @param Collection $list
     * @param $customerProfile
     * @return Collection
     */
    private function attachCustomerProfile(Collection $list, $customerProfile)
    {
        $list = $list->map(function ($item) use ($customerProfile) {
            $profile = $customerProfile->where('customer_id', (int)$item['party_id']);
            $cus = $profile->map(
                function($items) use ($item) {
                    $item['customer_name'] = isset($items->nick_name) ? $items->nick_name : $items->customer->profile->name ;
                    $item['customer_mobile'] =  $items->customer->profile->mobile;
                    $item['avatar'] = $items->customer->profile->pro_pic;
                    $item['customer_id'] = $items->customer_id;
                    $item['is_supplier'] = $items->is_supplier;
                    return $item;
                }
            );
            return $cus->count() > 0 ? call_user_func_array('array_merge', $cus->toArray()) : [];
        });
        return $list;
    }

    /**
     * @param Request $request
     * @param $url
     * @return mixed|string
     */
    private function updateRequestParam(Request $request, $url)
    {
        $order_by = $request->order_by;
        if (!empty($order_by) && $order_by != "name") {
            $order = !empty($request->order) ? strtolower($request->order) : 'desc';
            $url .= "&order_by=$order_by&order=$order";
        }

        if ($request->has('balance_type')) {
            $url .= "&balance_type=$request->balance_type";
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $url .= "&start_date=$request->start_date&end_date=$request->end_date";
        }

        if (($request->has('download_pdf')) && ($request->download_pdf == 1) ||
            ($request->has('share_pdf')) && ($request->share_pdf == 1)) {
            return $url;
        }

        if ($request->has('limit') && $request->has('offset')) {
            $url .= "&limit=$request->limit&offset=$request->offset";
        }
        return $url;
    }

    private function createEntryData(Request $request, $type)
    {
        $data['created_from'] = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount'] = (double)$request->amount;
        $data['source_type'] = $type;
        $data['note'] = $request->note;
        $data['debit_account_key'] = $type === EntryTypes::DUE ? $request->customer_id : $request->account_key;
        $data['credit_account_key'] = $type === EntryTypes::DUE ? $request->account_key : $request->customer_id;
        $data['customer_id'] = $request->customer_id;
        $data['customer_name'] = $request->customer_name;
        $data['entry_at'] = $request->date ?: Carbon::now()->format('Y-m-d H:i:s');
        $data['attachments'] = $this->uploadAttachments($request);
        return $data;
    }

    /**
     * @param $partner
     * @return array
     */
    private function getPartnerInfo($partner): array
    {
        return [
            'name' => $partner->name,
            'avatar' => $partner->logo,
            'mobile' => $partner->mobile,
        ];
    }
}