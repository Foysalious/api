<?php

namespace App\Sheba\AccountingEntry\Repository;

use App\Models\PosOrder;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\RequestIdentification;
use Sheba\Pos\Customer\PosCustomerResolver;

class AccountingDueTrackerRepository extends BaseRepository
{
    private $partner;

    /**
     * @param $partner
     * @return $this
     */
    public function setPartner($partner): AccountingDueTrackerRepository
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param Request $request
     * @param $type
     * @param bool $with_update
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function storeEntry(Request $request, $type, bool $with_update = false)
    {
        //todo: Should use AccountingRepository@storeEntry method for storing entry
        if (!$this->isMigratedToAccounting($this->partner->id)) {
            return true;
        }
        $this->getCustomer($request);
        $this->setModifier($request->partner);
        $request->merge(['source_id' => $this->posOrderId($request->partner, $request->partner_wise_order_id) ?? null]);
        $data = $this->createEntryData($request, $type);
        $url = $with_update ? "api/entries/" . $request->entry_id : "api/entries/";
        $data = $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->post($url, $data);
        // if type deposit then auto reconcile happen. for that we have to reconcile pos order.
        if ($type == "deposit") {
            foreach ($data as $datum) {
                if ($datum['source_type'] == 'pos' && $datum['amount_cleared'] > 0) {
                    $this->createPosOrderPayment($datum['amount_cleared'], $datum['source_id'], 'advance_balance');
                }
            }
        }
        return $data;
    }

    /**
     * @param $customerId
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function deleteCustomer($customerId)
    {
        if (!$this->isMigratedToAccounting($this->partner->id)) {
            return true;
        }
        $url = "api/due-list/" . $customerId;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->delete($url);
    }

    /**
     * @param $request
     * @param false $paginate
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getDueList($request, bool $paginate = false): array
    {
        $url = "api/due-list?";
        $url = $this->updateRequestParam($request, $url);
        $list = $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
        if (!empty($order_by) && $order_by == "name") {
            $order = ($request->order == 'desc') ? 'sortByDesc' : 'sortBy';
            $list = $list->$order('customer_name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        }
        return $list;
    }

    /**
     * @param $request
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getDuelistBalance($request): array
    {
        $url = "api/due-list/balance";
        $result = $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
        return [
            'total_transactions' => $result['total_transactions'],
            'total' => $result['total'],
            'stats' => $result['stats'],
            'partner' => $this->getPartnerInfo($request->partner),
        ];
    }

    /**
     * @param $request
     * @param $customerId
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getDueListByCustomer($request, $customerId): array
    {
        $url = "api/due-list/" . $customerId . "?";
        $url = $this->updateRequestParam($request, $url);
        $result = $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
        $due_list = collect($result['list']);

        $list = $due_list->map(
            function ($item) {
                if ($item["attachments"]) {
                    $item["attachments"] = is_array($item["attachments"]) ? $item["attachments"] : json_decode($item["attachments"]);
                }
                $item['created_at'] = Carbon::parse($item['created_at'])->format('Y-m-d h:i A');
                $item['entry_at'] = Carbon::parse($item['entry_at'])->format('Y-m-d h:i A');
                $pos_order = PosOrder::withTrashed()->find($item['source_id']);
                $item['partner_wise_order_id'] = isset($pos_order) ? $pos_order->partner_wise_order_id : null;
                if ($pos_order) {
                    $item['source_type'] = 'PosOrder';
                    if ($pos_order->sales_channel == SalesChannels::WEBSTORE) {
                        $item['source_type'] = 'WebstoreOrder';
                        $item['head'] = 'Webstore sales';
                        $item['head_bn'] = 'ওয়েবস্টোর সেলস';
                    }
                }
                return $item;
            }
        );
        return [
            'list' => $list
        ];
    }

    /**
     * @param $customerId
     * @return array
     * @throws AccountingEntryServerError
     * @throws InvalidPartnerPosCustomer
     * @throws \Exception
     */
    public function dueListBalanceByCustomer($customerId): array
    {
        $url = "api/due-list/" . $customerId . "/balance";
        $result = $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
        $customer = $result['customer'];
        if (is_null($customer)) {
            /** @var PosCustomerResolver $posCustomerResolver */
            $posCustomerResolver = app(PosCustomerResolver::class);
            $customer = $posCustomerResolver->setCustomerId($customerId)->setPartner($this->partner->id)->get();
            if (empty($customer)) {
                throw new InvalidPartnerPosCustomer();
            }
        }

        $total_debit = $result['other_info']['total_debit'];
        $total_credit = $result['other_info']['total_credit'];
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

        if ($request->has('filter_by_supplier') && $request->filter_by_supplier == 1) {
            $url .= "&filter_by_supplier=$request->filter_by_supplier";
        }

        if ($request->has('q')) {
            $url .= "&q=$request->q";
        }

        if ($request->has('limit') && $request->has('offset')) {
            $url .= "&limit=$request->limit&offset=$request->offset";
        }
        return $url;
    }

    /**
     * @param Request $request
     * @param $type
     * @return array
     */
    private function createEntryData(Request $request, $type): array
    {
        $data['created_from'] = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount'] = (double)$request->amount;
        $data['source_type'] = $type;
        $data['note'] = $request->note ?? null;
        $data['debit_account_key'] = $type === EntryTypes::DUE ? $request->customer_id : $request->account_key;
        $data['credit_account_key'] = $type === EntryTypes::DUE ? (new Accounts())->income->sales::DUE_SALES_FROM_DT : $request->customer_id;
        $data['customer_id'] = $request->customer_id;
        $data['customer_name'] = $request->customer_name;
        $data['customer_mobile'] = $request->customer_mobile;
        $data['customer_pro_pic'] = $request->pro_pic;
        $data['source_id'] = $request->source_id;
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

    /**
     * @param $partner
     * @param $partnerWiseOrderId
     * @return int|null
     */
    private function posOrderId($partner, $partnerWiseOrderId)
    {
        try {
            $posOrder = PosOrder::where('partner_id', $partner->id)->where('partner_wise_order_id', $partnerWiseOrderId)->first();
            return $posOrder->id;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updateDueDate($customerId, $partnerId, array $data)
    {
        /** @var AccountingCustomerRepository $customerRepo */
        $customerRepo = app(AccountingCustomerRepository::class);
        return $customerRepo->setUserId($partnerId)->updateCustomer($customerId, $data);
    }

    public function dueDateWiseCustomerList()
    {
        $url = "api/due-list/due-date-wise-customer-list";
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
    }
}