<?php namespace Sheba\DueTracker;

use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\Profile;
use App\Repositories\FileRepository;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use App\Sheba\DueTracker\Exceptions\InsufficientBalance;
use App\Sheba\Partner\PackageFeatureCount;
use Exception;
use Sheba\AccountingEntry\Exceptions\MigratedToAccountingException;
use Sheba\Pos\Customer\PosCustomerResolver;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Illuminate\Support\Facades\Log;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\SmsPurchase;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\BaseRepository;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
use Sheba\PaymentLink\Creator as PaymentLinkCreator;
use Sheba\RequestIdentification;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletDebitForbiddenException;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\Pos\Repositories\PosOrderPaymentRepository;

class DueTrackerRepository extends BaseRepository
{
    use ModificationFields, CdnFileManager, FileManager;

    public function getDueList(Request $request, $paginate = true)
    {
        $url      = "accounts/$this->accountId/entries/due-list?";
        $url      = $this->updateRequestParam($request, $url);
        $order_by = $request->order_by;
        $result   = $this->client->get($url);
        /** @var Collection $list */
        $list = $this->attachProfile(collect($result['data']['list']));
        if ($request->filled('balance_type') && in_array($request->balance_type, [
                'due',
                'received',
                'clear'
            ])) {
            $list = $list->where('balance_type', $request->balance_type)->values();
        }
        if ($request->filled('filter_by_supplier') && $request->filter_by_supplier == 1) {
            $list = $list->where('is_supplier', 1)->values();
        }
        if ($request->filled('q') && !empty($request->q)) {
            $query = trim($request->q);
            $list  = $list->filter(function ($item) use ($query) {
                return strpos(strtolower($item['customer_name']), "$query") !== false || strpos(strtolower($item['customer_mobile']), "$query") !== false;
            })->values();
        }
        if (!empty($order_by) && $order_by == "name") {
            $order = ($request->order == 'desc') ? 'sortByDesc' : 'sortBy';
            $list  = $list->$order('customer_name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        }
        $total = $list->count();
        if ($paginate && isset($request['offset']) && isset($request['limit'])) {
            list($offset, $limit) = calculatePagination($request);
            $list = $list->slice($offset)->take($limit)->values();
        }
        return [
            'list'               => $list,
            'total_transactions' => count($list),
            'total'              => $total,
            'stats'              => $result['data']['totals'],
            'partner'            => $this->getPartnerInfo($request->partner),
        ];
    }

    private function updateRequestParam(Request $request, $url)
    {
        $order_by = $request->order_by;
        if (!empty($order_by) && $order_by != "name") {
            $order = !empty($request->order) ? strtolower($request->order) : 'desc';
            $url   .= "&order_by=$order_by&order=$order";
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $url .= "&start=$request->start_date&end=$request->end_date";
        }
        return $url;
    }

    private function attachProfile(Collection $list)
    {
        $list = $list->map(function ($item) {
            /** @var Profile $profile */
            $profile    = Profile::select('name', 'mobile', 'id', 'pro_pic')->find($item['profile_id']);
            $customerId = $profile && isset($profile->posCustomer) ? $profile->posCustomer->id : null;

            if (isset($customerId)) {
                $posProfile = PartnerPosCustomer::byPartner($this->partnerId)->where('customer_id', $customerId)->first();
            }

            if (isset($posProfile) && isset($posProfile->nick_name)) {
                $item['customer_name'] = $posProfile->nick_name;
            } else {
                $item['customer_name'] = $profile ? $profile->name : "Unknown";
            }


            $item['customer_mobile'] = $profile ? $profile->mobile : null;
            $item['avatar']          = $profile ? $profile->pro_pic : null;
            $item['customer_id']     = $customerId;
            $item['is_supplier']     = isset($posProfile) ? $posProfile->is_supplier : 0;
            return $item;
        });
        return $list;
    }

    private function attachCustomerProfile(Collection $list, $customerProfile)
    {
        return $list->map(function ($item) use ($customerProfile) {
            $profile = $customerProfile->where('customer.profile.id', $item['profile_id']);
            $cus     = $profile->map(
                function ($items) use ($item) {
                    $item['customer_name']   = $items->nick_name ?? $items->customer->profile->name;
                    $item['customer_mobile'] = $items->customer->profile->mobile;
                    $item['avatar']          = $items->customer->profile->pro_pic;
                    $item['customer_id']     = $items->customer_id;
                    $item['is_supplier']     = $items->is_supplier;
                    return $item;
                }
            );
            return call_user_func_array('array_merge', $cus->toArray());
        });
    }

    /**
     * @param Partner $partner
     * @param $customerId
     * @param $request
     * @return array
     * @throws ExpenseTrackingServerError
     * @throws InvalidPartnerPosCustomer
     */
    public function getDueListByProfile(Partner $partner, $customerId, $request = null): array
    {
        $partner_pos_customer = PartnerPosCustomer::byPartner($partner->id)->where('customer_id', $customerId)->with(['customer'])->first();
        $customer             = $partner_pos_customer->customer ?? PosCustomer::find($customerId);
        if (empty($customer)) {
            throw new InvalidPartnerPosCustomer();
        }
        $url      = "accounts/$this->accountId/entries/due-list/$customer->profile_id?";
        $url      = $request ? $this->updateRequestParam($request, $url) : $url;
        $result   = $this->client->get($url);
        $due_list = collect($result['data']['list']);
        if ($request && isset($request['offset'], $request['limit'])) {
            list($offset, $limit) = calculatePagination($request);
            $due_list = $due_list->slice($offset)->take($limit)->values();
        }
        $list = $due_list->map(function ($item) {
            $item['created_at']            = Carbon::parse($item['created_at'])->format('Y-m-d h:i A');
            $item['entry_at']              = Carbon::parse($item['entry_at'])->format('Y-m-d h:i A');
            $pos_order                     = PosOrder::withTrashed()->find($item['source_id']);
            $item['partner_wise_order_id'] = $item['source_type'] === 'PosOrder' && $pos_order ? $pos_order->partner_wise_order_id : null;
            if ($pos_order && $pos_order->sales_channel === SalesChannels::WEBSTORE) {
                $item['source_type'] = 'WebstoreOrder';
                $item['head']        = 'Webstore sales';
                $item['head_bn']     = '?????????????????????????????? ????????????';
            }
            return $item;
        });

        $total_credit       = 0;
        $total_debit        = 0;
        $total_transactions = count($list);
        foreach ($list as $item) {
            if ($item['type'] === 'deposit') {
                $total_debit += $item['amount'];
            } else {
                $total_credit += $item['amount'];
            }
        }
        return [
            'list'       => $list,
            'stats'      => $result['data']['totals'],
            'customer'   => [
                'id'                => $customer->id,
                'name'              => !empty($partner_pos_customer) && $partner_pos_customer->nick_name ? $partner_pos_customer->nick_name : $customer->profile->name,
                'mobile'            => $customer->profile->mobile,
                'avatar'            => $customer->profile->pro_pic,
                'due_date_reminder' => $partner_pos_customer->due_date_reminder ?? null,
                'is_supplier'       => $partner_pos_customer->is_supplier ?? 0
            ],
            'partner'    => $this->getPartnerInfo($partner),
            'other_info' => [
                'total_transactions' => $total_transactions,
                'total_credit'       => $total_credit,
                'total_debit'        => $total_debit,
            ],
            'balance'    => [
                'amount' => abs($total_debit - $total_credit),
                'type'   => $total_debit > $total_credit ? 'Advance' : 'Due',
                'color'  => $total_debit > $total_credit ? '#219653' : '#DC1E1E'

            ]
        ];
    }

    /**
     * @param $partner
     * @return array
     */
    private function getPartnerInfo($partner)
    {
        return [
            'name'   => $partner->name,
            'avatar' => $partner->logo,
            'mobile' => $partner->mobile,
        ];
    }

    /**
     * @param Partner $partner
     * @param Request $request
     * @return array|bool
     * @throws ExpenseTrackingServerError|MigratedToAccountingException
     */
    public function store(Partner $partner, Request $request)
    {
        if ($this->isMigratedToAccounting()) {
            return true;
        }
        $partner_pos_customer = PartnerPosCustomer::byPartner($partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if (empty($partner_pos_customer))
            $partner_pos_customer = PartnerPosCustomer::create(['partner_id' => $partner->id, 'customer_id' => $request->customer_id]);
        /** @var PosCustomer $customer */
        $customer = $partner_pos_customer->customer;
        $this->setModifier($partner);
        $data     = $this->createStoreData($request);
        $response = $this->client->post("accounts/$this->accountId/entries/due-store/$customer->profile_id", $data);
        Log::info(['response from expense server', $response]);
        return $response['data'];
    }

    /**
     * @param Partner $partner
     * @param Request $request
     * @return mixed
     * @throws ExpenseTrackingServerError|MigratedToAccountingException
     */
    public function update(Partner $partner, Request $request)
    {
        if ($this->isMigratedToAccounting()) {
            return true;
        }
        $partner_pos_customer = PartnerPosCustomer::byPartner($partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if (empty($partner_pos_customer))
            $partner_pos_customer = PartnerPosCustomer::create(['partner_id' => $partner->id, 'customer_id' => $request->customer_id]);
        $this->setModifier($partner);
        if ($request->filled('amount'))
            $data['amount'] = $request->amount;
        if ($request->filled('note'))
            $data['note'] = $request->note;
        if ($request->filled('created_at'))
            $data['created_at'] = $request->created_at;
        if ($request->hasFile('attachments')) {
            $data['attachments'] = $this->updateAttachments($request);
        }
        $data['amount_cleared'] = $request->filled('amount_cleared') ? $request->amount_cleared : 0;
        $data['created_from']   = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['updated_at']     = $request->updated_at ?: Carbon::now()->format('Y-m-d H:i:s');

        $response = $this->client->post("accounts/$this->accountId/entries/update/$request->entry_id", $data);

        if ($data['amount_cleared'] > 1 && $response['data']['source_type'] == 'PosOrder' && !empty($response['data']['source_id']))
            $this->createPosOrderPayment($data['amount_cleared'], $response['data']['source_id'], 'cod');

        return $response['data'];
    }

    public function createPosOrderPayment($amount_cleared, $pos_order_id, $payment_method)
    {
        if ($this->isMigratedToAccounting()) {
            return true;
        }
        /** @var PosOrder $order */
        $order = PosOrder::find($pos_order_id);
        if (isset($order)) {
            $order->calculate();
            if ($order->getDue() > 0) {
                $payment_data['pos_order_id'] = $pos_order_id;
                $payment_data['amount']       = $amount_cleared;
                $payment_data['method']       = $payment_method;
                /** @var PaymentCreator $paymentCreator */
                $paymentCreator = app(PaymentCreator::class);
                $paymentCreator->credit($payment_data);
                $order->calculate();
            }
        }
    }

    public function removePosOrderPayment($pos_order_id, $amount)
    {
        if ($this->isMigratedToAccounting()) {
            return true;
        }
        $payment = PosOrderPayment::where('pos_order_id', $pos_order_id)
            ->where('amount', $amount)
            ->where('transaction_type', 'Credit')
            ->first();

        if ($payment) {
            $payment->delete();
            /** @var PosOrder $order */
            $order = PosOrder::find($pos_order_id);
            $order->calculate();
            return true;
        }
        return false;
    }

    private function createStoreData(Request $request)
    {
        $data['created_from']   = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount']         = (double)$request->amount;
        $data['note']           = $request->note;
        $data['amount_cleared'] = 0;
        $data['type']           = $request->type == 'due' ? 'income' : 'expense';
        $data['head_name']      = AutomaticIncomes::DUE_TRACKER;
        $data['created_at']     = $request->created_at ?: Carbon::now()->format('Y-m-d H:i:s');
        $data['attachments']    = $this->uploadAttachments($request);
        $data['payment_method'] = 'cod';
        return $data;
    }

    private function uploadAttachments(Request $request)
    {
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $key => $file) {
                if (!empty($file)) {
                    list($file, $filename) = $this->makeAttachment($file, '_' . getFileName($file) . '_attachments');
                    $attachments[] = $this->saveFileToCDN($file, getDueTrackerAttachmentsFolder(), $filename);
                }
            }
        }
        return json_encode($attachments);
    }

    /**
     * @param Request $request
     * @return false|string
     */
    private function updateAttachments(Request $request)
    {
        $attachments = [];
        foreach ($request->file('attachments') as $key => $file) {
            list($file, $filename) = $this->makeAttachment($file, '_' . getFileName($file) . '_attachments');
            $attachments[] = $this->saveFileToCDN($file, getDueTrackerAttachmentsFolder(), $filename);;
        }

        $old_attachments = $request->old_attachments ?: [];
        if ($request->filled('attachment_should_remove') && (!empty($request->attachment_should_remove))) {
            $this->deleteFromCDN($request->attachment_should_remove);
            $old_attachments = array_diff($old_attachments, $request->attachment_should_remove);
        }

        $attachments = array_filter(array_merge($attachments, $old_attachments));
        return json_encode($attachments);

    }

    /**
     * @param $files
     */
    private function deleteFromCDN($files)
    {
        foreach ($files as $file) {
            $filename = substr($file, strlen(env('S3_URL')));
            (new FileRepository())->deleteFileFromCDN($filename);
        }
    }

    /**
     * @param array $list
     * @param Partner $partner
     * @return mixed
     */
    public function generateDueReminders(array $list, Partner $partner)
    {
        $response['today']    = [];
        $response['previous'] = [];
        $response['next']     = [];
        foreach ($list['list'] as $item) {
            $partner_pos_customer = PartnerPosCustomer::with([
                'customer' => function ($q) {
                    $q->select('id', 'profile_id');
                }
            ])->byPartnerAndCustomer($partner->id, $item['customer_id'])->first();

            $due_date_reminder = $partner_pos_customer['due_date_reminder'];
            if ($partner_pos_customer && $due_date_reminder) {
                $temp['customer_name']     = $item['customer_name'];
                $temp['customer_id']       = $item['customer_id'];
//                $temp['profile_id']        = $partner_pos_customer->customer->profile_id;
                $temp['phone']             = $partner_pos_customer->details()['phone'];
                $temp['balance']           = $item['balance'];
                $temp['due_date_reminder'] = $due_date_reminder;

                if (Carbon::parse($due_date_reminder) == Carbon::parse(Carbon::today())) {
                    array_push($response['today'], $temp);
                } else if (Carbon::parse($due_date_reminder) < (Carbon::today())) {
                    array_push($response['previous'], $temp);
                } else if ((Carbon::parse($due_date_reminder)) > (Carbon::today())) {
                    array_push($response['next'], $temp);
                }
            }
        }
        return $response;
    }

    /**
     * @param array $dueList
     * @param Request $request
     * @return array
     */
    public function generateDueCalender(array $dueList, Request $request)
    {
        $calender = [];

        foreach ($dueList['list'] as $item) {
            $partner_pos_customer = PartnerPosCustomer::byPartnerAndCustomer($request->partner->id, $item['customer_id'])->first();
            $due_date_reminder    = $partner_pos_customer['due_date_reminder'];
            if ($partner_pos_customer && $due_date_reminder) {
                $year  = Carbon::parse($due_date_reminder)->year;
                $month = Carbon::parse($due_date_reminder)->month;
                $day   = Carbon::parse($due_date_reminder)->day;
                if ($year == $request->year && $month == $request->month) {
                    if (!isset($calender[$day])) $calender[$day] = [];
                    array_push($calender[$day], $item);
                }
            }
        }
        $response = [];
        foreach ($calender as $key => $items) {
            $data['date']      = Carbon::create($request->year, $request->month, $key)->format('d-m-Y');
            $data['count']     = count($items);
            $data['customers'] = [];
            foreach ($items as $item) {
                $temp['customer_name'] = $item['customer_name'];
                $temp['customer_id']   = $item['customer_id'];
//                $temp['profile_id']    = $item['profile_id'];
                $temp['balance'] = $item['balance'];
                array_push($data['customers'], $temp);
            }
            array_push($response, $data);
        }
        return $response;
    }

    /**
     * @param $entry_id
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function removeEntry($entry_id)
    {
        return $this->client->delete("accounts/$this->accountId/entries/$entry_id");
    }

    /**
     * @param $profile_id
     * @throws ExpenseTrackingServerError
     */
    public function removeCustomer($profile_id)
    {
        $url = "accounts/$this->accountId/remove/$profile_id";
        $this->client->delete($url);

    }
    private function getCustomer($partner,$customer_id){
        /** @var PosCustomerResolver $posCustomerResolver */
        $posCustomerResolver = app(PosCustomerResolver::class);
        return $posCustomerResolver->setCustomerId($customer_id)->setPartner($partner)->get();
    }
    /**
     * @param Request $request
     * @return bool
     * @throws InvalidPartnerPosCustomer
     * @throws Exception
     */
    public function sendSMS(Request $request)
    {
        $customer=$this->getCustomer($request->partner,$request->customer_id);
        if (empty($customer)) throw new InvalidPartnerPosCustomer();
        /** @var PosCustomer $customer */
        $smsType = [
            'receivable' => 'due',
            'payable'    => 'deposit',
            'due'        => 'due',
            'deposit'    => 'deposit',
        ];
        $type    = $smsType[$request->type];

        /** @var Partner $partner */
        $partner = $request->partner;
        $data    = [
            'type'           => $type,
            'partner_name'   => $partner->name,
            'customer_name'  => $customer->name,
            'mobile'         => $customer->mobile,
            'amount'         => $request->amount,
            'company_number' => $partner->getContactNumber()
        ];
        if ($request->filled('payment_link')) {
            $data['payment_link'] = $request->payment_link;
        }
        /** @var SmsHandlerRepo $sms */
        list($sms, $log) = $this->getSms($data);
        $sms_cost = $sms->getSmsCountAndEstimationCharge();
        /** @var PackageFeatureCount $packageFeatureCount */
        $packageFeatureCount = app(PackageFeatureCount::class)->setPartnerId($request->partner->id)
            ->setFeature('sms');
        $isEligible = $packageFeatureCount
            ->isEligible($sms_cost['sms_count']);
        if ($isEligible) {
            $sms->shoot();
            $packageFeatureCount->decrementFeatureCount($sms_cost['sms_count']);
            return true;
        }
        return false;

    }

    public function getSms($data)
    {
        $log          = " BDT has been deducted for sending ";
        $message_data = [
            'customer_name'  => $data['customer_name'],
            'partner_name'   => $data['partner_name'],
            'amount'         => $data['amount'],
            'company_number' => $data['company_number']
        ];

        if ($data['type'] == 'due') {
            $sms                          = (new SmsHandlerRepo('inform-due'));
            $message_data['payment_link'] = $data['payment_link'];
            $log                          .= "due details";
        } else {
            $sms = (new SmsHandlerRepo('inform-deposit'));
            $log .= "deposit details";
        }

        $sms = $sms
            ->setMobile($data['mobile'])
            ->setMessage($message_data)
            ->setFeatureType(FeatureType::DUE_TRACKER)
            ->setBusinessType(BusinessType::SMANAGER);
        return [$sms, $log];
    }

    /**
     * @return array
     */
    public function getFaqs()
    {
        return [
            [
                'question' => '??????????????? ???????????? ???????',
                'answer'   => '??????????????? ???????????? ??????????????? ????????????/???????????? ??????????????? ??????????????? ????????????????????? ????????????????????? ??????????????? ???????????? ???????????? ????????? ???????????? ??????????????? ?????????????????? ?????????????????? ????????? ???????????????'
            ],
            [
                'question' => '?????????????????? ??????????????? ???????????? ????????????????????? ??????????',
                'answer'   => '???????????? ??????????????? ??????????????? ???????????? ???????????????????????? ????????? ????????? ???????????? ???????????????????????? ??????????????? ???????????? ???????????????????????? ????????????????????? ????????? ????????????/???????????? ??????????????? ???????????? ????????????????????? ???????????? ????????????/???????????? ??????????????? ??????????????????, ?????????,??????????????? ????????? ????????? ????????? ???????????? ????????????????????? ????????????????????? ????????? ???????????? ?????????????????????'
            ],
            [
                'question' => '????????? ???????????? ???????',
                'answer'   => '???????????????????????? ?????? ????????? ???????????? ????????? ??????????????? ?????????????????????'
            ],
            [
                'question' => '????????? ????????? ???????',
                'answer'   => '???????????????????????? ?????? ????????? ???????????? ????????? ???????????? ?????????????????????'
            ],
            [
                'question' => '??????????????? ?????????????????????????????? ???????',
                'answer'   => '??????????????? ?????????????????????????????? ???????????? ???????????????????????? ?????????????????? ????????? ???????????? ?????????????????? ???????????? ?????? ??????????????? ?????????????????????'
            ],
            [
                'question' => 'POS ???????????? ?????????????????? ????????? ???????????? ???????????? ??????????????? ??????????????? ???????????? ???????',
                'answer'   => '??????????????? ???????????????'
            ]
        ];
    }

    private function setSmsData($request, $customer) {
        return [
            'type'          => $request->type,
            'partner_name'  => $request->partner->name,
            'customer_name' => $customer->profile->name,
            'mobile'        => $customer->profile->mobile,
            'amount'        => $request->amount,
            'payment_link'  => $request->type == 'due' ? $request->payment_link : null
        ];
    }

    /**
     * @param Request $request
     * @param PaymentLinkCreator $paymentLinkCreator
     * @return mixed
     * @throws Exception
     */
    public function createPaymentLink(Request $request, $paymentLinkCreator)
    {
        $purpose            = 'Due Collection';
        $customer           = $this->getCustomer($request->partner,$request->customer_id);
       if (empty($customer)){
           throw new InvalidPartnerPosCustomer();
       }
        $payment_link_store = $paymentLinkCreator->setAmount($request->amount)
            ->setReason($purpose)
            ->setUserName($request->partner->name)
            ->setUserId($request->partner->id)
            ->setUserType('partner')
            ->setTargetType('due_tracker')
            ->setTargetId(1)
            ->setPayerId($customer->id)
            ->setPayerType('pos_customer')
            ->save();

        if ($payment_link_store) {
            return $paymentLinkCreator->getPaymentLink();
        }

        throw new Exception('payment link creation fail');
    }
}
