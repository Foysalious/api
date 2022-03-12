<?php

namespace App\Sheba\AccountingEntry\Repository;

use App\Exceptions\Pos\Customer\PosCustomerNotFoundException;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\Pos\Order\PosOrderObject;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\RequestIdentification;
use Sheba\Pos\Customer\PosCustomerResolver;
use Sheba\Pos\Order\PosOrderResolver;
use App\Sheba\PosOrderService\Services\OrderService as OrderServiceAlias;


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
     * @throws AccountingEntryServerError|PosCustomerNotFoundException
     * @throws Exception
     */
    public function storeEntry(Request $request, $type, bool $with_update = false)
    {
        //todo: Should use AccountingRepository@storeEntry method for storing entry
        if (!$this->isMigratedToAccounting($this->partner->id)) {
            return true;
        }
        $this->getCustomer($request);
        $this->setModifier($request->partner);
        $posOrder = ($type == EntryTypes::POS) ? $this->posOrderByPartnerWiseOrderId($request->partner, $request->partner_wise_order_id) : null;
        $request->merge(['source_id' =>  $posOrder ? $posOrder->id : null]);
        $data = $this->createEntryData($request, $type, $with_update);
        if (!$request->customer_id && !$request->customer_name) {
            throw new PosCustomerNotFoundException('Sorry! cannot create entry without customer', 404);
        }
        $url = $with_update ? "api/entries/" . $request->entry_id : "api/entries/";
        return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->post($url, $data);
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
        $url = "api/due-list/?";
        $url = $this->updateRequestParam($request, $url);
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
    }

    /**
     * @param $request
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getDuelistBalance($request): array
    {
        $url = "api/due-list/balance?";
        $url=$this->updateRequestParam($request, $url);
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
        $due_list = $result['list'];
        $pos_orders = [];
        $orders = [];
        foreach ($due_list as $key => $item) {
            if ($item["attachments"]) {
                $item["attachments"] = is_array($item["attachments"]) ? $item["attachments"] : json_decode($item["attachments"]);
            }
            $item['created_at'] = Carbon::parse($item['created_at'])->format('Y-m-d h:i A');
            $item['entry_at'] = Carbon::parse($item['entry_at'])->format('Y-m-d h:i A');
            if ($item['source_id'] && $item['source_type'] == EntryTypes::POS) {
                $pos_orders[] =  $item['source_id'];
            }
            $due_list[$key]['partner_wise_order_id']= null;
        }
        if (count($pos_orders) > 0) {
            $orders = $this->getPartnerWise($pos_orders)['orders'];
        }

        foreach ($due_list as $key => $val) {
            if ($val['source_id'] && $val['source_type'] == EntryTypes::POS && count($orders) > 0) {
                if (isset($orders[$val['source_id']])) {
                    $order = $orders[$val['source_id']];
                    $due_list[$key]['partner_wise_order_id'] = $order['partner_wise_order_id'];
                    $due_list[$key]['source_type'] = 'PosOrder';
                    $due_list[$key]['head'] = 'POS sales';
                    $due_list[$key]['head_bn'] = 'সেলস';
                    if (isset($order['sales_channel']) == SalesChannels::WEBSTORE) {
                        $due_list[$key]['source_type'] = 'Webstore Order';
                        $due_list[$key]['head'] = 'Webstore sales';
                        $due_list[$key]['head_bn'] = 'ওয়েবস্টোর সেলস';
                    }
                }

            }
        }
        return [
            'list' => $due_list
        ];
    }

    /**
     * @param $customerId
     * @param null $request
     * @return array
     * @throws AccountingEntryServerError
     * @throws InvalidPartnerPosCustomer
     */
    public function dueListBalanceByCustomer($customerId,$request=null): array
    {
        $url = "api/due-list/" . $customerId . "/balance?";
        if ($request){
            $url=$this->updateRequestParam($request, $url);
        }
        $result = $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
        $customer = [];

        if (is_null($result['customer'])) {
            /** @var PosCustomerResolver $posCustomerResolver */
            $posCustomerResolver = app(PosCustomerResolver::class);
            $posCustomer = $posCustomerResolver->setCustomerId($customerId)->setPartner($this->partner)->get();
            if (empty($posCustomer)) {
                throw new InvalidPartnerPosCustomer();
            }
            $customer['id'] = $posCustomer->id;
            $customer['name'] = $posCustomer->name;
            $customer['mobile'] = $posCustomer->mobile;
            $customer['avatar'] = $posCustomer->pro_pic;
            $customer['due_date_reminder'] = null;
            $customer['is_supplier'] = $posCustomer->is_supplier;
        } else {
            $customer['id'] = $result['customer']['id'];
            $customer['name'] = $result['customer']['name'];
            $customer['mobile'] = $result['customer']['mobile'];
            $customer['avatar'] = $result['customer']['proPic'];
            $customer['due_date_reminder'] = $result['customer']['dueDateReminder'];
            $customer['is_supplier'] = $result['customer']['isSupplier'] ? 1 : 0;
        }

        $total_debit = $result['other_info']['total_debit'];
        $total_credit = $result['other_info']['total_credit'];
        $result['balance']['color'] = $total_debit > $total_credit ? '#219653' : '#DC1E1E';
        return [
            'customer' => $customer,
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
        if (!empty($order_by)) {
            $order = !empty($request->order) ? strtolower($request->order) : 'desc';
            $url .= "&order_by=$order_by&order=$order";
        }

        if ($request->filled('balance_type')) {
            $url .= "&balance_type=$request->balance_type";
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $url .= "&start_date=$request->start_date&end_date=$request->end_date";
        }

        if (($request->filled('download_pdf')) && ($request->download_pdf == 1) ||
            ($request->filled('share_pdf')) && ($request->share_pdf == 1)) {
            return $url;
        }

        if ($request->filled('filter_by_supplier') && $request->filter_by_supplier == 1) {
            $url .= "&filter_by_supplier=$request->filter_by_supplier";
        }

        if ($request->filled('q')) {
            $url .= "&q=$request->q";
        }

        if ($request->filled('limit') && $request->filled('offset')) {
            $url .= "&limit=$request->limit&offset=$request->offset";
        }
        return $url;
    }

    /**
     * @param Request $request
     * @param $type
     * @param bool $withUpdate
     * @return array
     */
    private function createEntryData(Request $request, $type, $withUpdate = false): array
    {
        $data['created_from'] = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount'] = (double)$request->amount;
        $data['source_type'] = $type;
        $data['note'] = $request->note ?? null;
        $data['debit_account_key'] = $type === EntryTypes::DUE ? $request->customer_id : $request->account_key;
        $data['credit_account_key'] = $type === EntryTypes::DUE ? (new Accounts())->income->sales::DUE_SALES_FROM_DT : $request->customer_id;
        $data['customer_id'] = $request->customer_id ?? null;
        $data['customer_name'] = $request->customer_name ?? null;
        $data['customer_mobile'] = $request->customer_mobile ?? null;
        $data['customer_pro_pic'] = $request->customer_pro_pic ?? null;
        $data['customer_is_supplier'] = $request->customer_is_supplier ?? null;
        $data['source_id'] = $request->source_id;
        $data['entry_at'] = $request->date ?: Carbon::now()->format('Y-m-d H:i:s');
        $data['attachments'] =$withUpdate?$this->updateAttachments($request): $this->uploadAttachments($request);
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
     * @return PosOrderObject
     */
    private function posOrderByPartnerWiseOrderId($partner, $partnerWiseOrderId)
    {
        try {
            /** @var PosOrderResolver $posOrderResolver */
            $posOrderResolver = app(PosOrderResolver::class);
            return $posOrderResolver->setPartnerWiseOrderId($partner->id, $partnerWiseOrderId)->get();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param null $orderId
     * @return PosOrderObject
     */
    private function posOrderByOrderId($orderId)
    {
        try {
            /** @var PosOrderResolver $posOrderResolver */
            $posOrderResolver = app(PosOrderResolver::class);
            return $posOrderResolver->setOrderId($orderId)->get();
        } catch (Exception $e) {
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

    private function getPartnerWise($pos_orders)
    {
        /** @var OrderServiceAlias $orderService */
        $orderService= app(OrderServiceAlias::class);
        return $orderService->getPartnerWiseOrderIds('[' . implode(",",$pos_orders) . ']' ,0,count($pos_orders));

    }
}