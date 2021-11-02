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
use Sheba\Pos\Payment\Creator as PaymentCreator;
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
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class DueTrackerRepository extends BaseRepository
{
    use ModificationFields, CdnFileManager, FileManager;

    public function getDueList(Request $request, $paginate = true)
    {
        $url      = "accounts/$this->accountId/entries/due-list?";
        $url      = $this->updateRequestParam($request, $url);
        $customerProfiles = null;
        if($request->has('q') && !empty($request->q)) {
            $profiles = PartnerPosCustomer::with([
                 'customer' => function($q) {
                     $q->select('id', 'profile_id');
                 },
                 'customer.profile' => function($q) {
                     $q->select('name', 'mobile', 'id', 'pro_pic');
                 }])->where('partner_id', $this->partnerId);

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
            $ids = $profiles->get()->pluck('customer.profile.id');
            $ids = implode(",", $ids->toArray());
            $url .= "&q=$ids";
        }
        $result = $this->client->get($url);
        if ($customerProfiles) {
            $list = $this->attachCustomerProfile(collect($result['data']['list']), $customerProfiles);
        } else {
            /** @var Collection $list */
            $list = $this->attachProfile(collect($result['data']['list']));
        }

        if($request->has('filter_by_supplier') && $request->filter_by_supplier == 1)
        {
            $list = $list->where('is_supplier', 1)->values();
        }
        if ($paginate && isset($request['offset']) && isset($request['limit'])) {
            list($offset, $limit) = calculatePagination($request);
            $list = $list->slice($offset)->take($limit)->values();
        }
        $total = $list->count();
        return [
            'list'               => $list,
            'total_transactions' => $total,
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
        if ($request->has('start_date') && $request->has('end_date')) {
            $url .= "&start=$request->start_date&end=$request->end_date";
        }
        if ($request->has('balance_type') && in_array($request->balance_type, ['due', 'received', 'clear'])) {
            $url .= "&balance_type=$request->balance_type";
        }
//        if (($request->has('download_pdf')) && ($request->download_pdf == 1) || ($request->has('share_pdf')) && ($request->share_pdf == 1)) {
//            return $url;
//        }
//        $request->has('limit') ? $url .= "&limit=$request->limit" : $url .= "&limit=20";
//        $request->has('offset') ? $url .= "&offset=$request->offset" : $url .= "&offset=0";
        return $url;
    }

    private function attachProfile(Collection $list)
    {
        $list = $list->map(function ($item) {
            /** @var Profile $profile */
            $profile                 = Profile::select('name', 'mobile', 'id', 'pro_pic')->find($item['profile_id']);
            $customerId              = $profile && isset($profile->posCustomer) ? $profile->posCustomer->id : null;

            if(isset($customerId)) {
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
            $item['is_supplier'] = isset($posProfile) ? $posProfile->is_supplier : 0;
            return $item;
        });
        return $list;
    }

    private function attachCustomerProfile(Collection $list, $customerProfile)
    {
        $list = $list->map(function ($item) use ($customerProfile) {
            $profile = $customerProfile->where('customer.profile.id', $item['profile_id']);
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
            return call_user_func_array('array_merge', $cus->toArray());
        });
        return $list;
    }

    /**
     * @param Partner $partner
     * @param Request $request
     * @param bool    $paginate
     * @return array
     * @throws ExpenseTrackingServerError
     * @throws InvalidPartnerPosCustomer
     */
    public function getDueListByProfile(Partner $partner, Request $request)
    {
        $partner_pos_customer = PartnerPosCustomer::byPartner($partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        $customer             = PosCustomer::find($request->customer_id);
        if (!empty($partner_pos_customer)) {
            $customer = $partner_pos_customer->customer;
        }
        if (empty($customer)) throw new InvalidPartnerPosCustomer();
        $url    = "accounts/$this->accountId/entries/due-list/$customer->profile_id?";
        $url    = $this->updateRequestParam($request, $url);
        $result = $this->client->get($url);
        $due_list = collect($result['data']['list']);
        if(isset($request['offset']) && isset($request['limit'])) {
            list($offset, $limit) = calculatePagination($request);
            $due_list               = $due_list->slice($offset)->take($limit)->values();
        }
        $list   = $due_list->map(function ($item) {
            $item['created_at'] = Carbon::parse($item['created_at'])->format('Y-m-d h:i A');
            $item['entry_at']   = Carbon::parse($item['entry_at'])->format('Y-m-d h:i A');
            $pos_order = PosOrder::withTrashed()->find($item['source_id']);
            $item['partner_wise_order_id'] = $item['source_type'] === 'PosOrder' && $pos_order ? $pos_order->partner_wise_order_id: null;
            if ($pos_order && $pos_order->sales_channel === SalesChannels::WEBSTORE) {
                $item['source_type'] = 'WebstoreOrder';
                $item['head'] = 'Webstore sales';
                $item['head_bn'] = 'ওয়েবস্টোর সেলস';
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
                'due_date_reminder' => !empty($partner_pos_customer) ? $partner_pos_customer->due_date_reminder : null,
                'is_supplier' => !empty($partner_pos_customer) ? $partner_pos_customer->is_supplier : 0
            ],
            'partner'    => $this->getPartnerInfo($partner),
            'other_info' => [
                'total_transactions' => $total_transactions,
                'total_credit'       => $total_credit,
                'total_debit'        => $total_debit,
            ],
            'balance' => [
                'amount' => abs($total_debit - $total_credit),
                'type' => $total_debit > $total_credit ? 'Advance' : 'Due',
                'color' => $total_debit > $total_credit ? '#219653' : '#DC1E1E'

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
     * @throws ExpenseTrackingServerError
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
        return $response['data'];
    }


    /**
     * @param Partner $partner
     * @param Request $request
     * @return mixed
     * @throws ExpenseTrackingServerError
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
        if ($request->has('amount'))
            $data['amount'] = $request->amount;
        if ($request->has('note'))
            $data['note'] = $request->note;
        if ($request->has('created_at'))
            $data['created_at'] = $request->created_at;
        if ($request->hasFile('attachments')) {
            $data['attachments'] = $this->updateAttachments($request);
        }
        $data['amount_cleared'] = $request->has('amount_cleared') ? $request->amount_cleared : 0;
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
        if(isset($order)) {
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

    public function removePosOrderPayment($pos_order_id, $amount){
        if ($this->isMigratedToAccounting()) {
            return true;
        }
        $payment = PosOrderPayment::where('pos_order_id', $pos_order_id)
            ->where('amount', $amount)
            ->where('transaction_type', 'Credit')
            ->first();

       if($payment)
       {
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
        if ($request->has('attachment_should_remove') && (!empty($request->attachment_should_remove))) {
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
     * @param array   $list
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
                 'customer' => function($q) {
                     $q->select('id', 'profile_id');
                 }])->byPartnerAndCustomer($partner->id, $item['customer_id'])->first();

            $due_date_reminder    = $partner_pos_customer['due_date_reminder'];
            if ($partner_pos_customer && $due_date_reminder) {
                $temp['customer_name']     = $item['customer_name'];
                $temp['customer_id']       = $item['customer_id'];
                $temp['profile_id']        = $partner_pos_customer->customer->profile_id;
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
     * @param array   $dueList
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
                $temp['profile_id']    = $item['profile_id'];
                $temp['balance']       = $item['balance'];
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

    /**
     * @param Request $request
     * @return mixed
     * @throws InvalidPartnerPosCustomer|InsufficientBalance
     * @throws \Sheba\Transactions\Wallet\WalletDebitForbiddenException
     */
    public function sendSMS(Request $request)
    {
        $partner_pos_customer = PartnerPosCustomer::byPartner($request->partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if (empty($partner_pos_customer)) throw new InvalidPartnerPosCustomer();
        /** @var PosCustomer $customer */
        $customer = $partner_pos_customer->customer;
        $type = $request->type == 'receivable' ? 'due' : 'deposit';
        /** @var Partner $partner */
        $partner=$request->partner;
        $data     = [
            'type'          => $type,
            'partner_name'  => $partner->name,
            'customer_name' => $customer->profile->name,
            'mobile'        => $customer->profile->mobile,
            'amount'        => $request->amount,
            'company_number'=> $partner->getContactNumber()
        ];

        if ($request->has('payment_link')) {
            $data['payment_link'] = $request->payment_link;
        }
        /** @var SmsHandlerRepo $sms */
        list($sms, $log) = $this->getSms($data);
        $sms_cost = $sms->estimateCharge();
        if ((double)$request->partner->wallet < $sms_cost) throw new InsufficientBalance();
        //freeze money amount check
        WalletTransactionHandler::isDebitTransactionAllowed($request->partner, $sms_cost, 'এস-এম-এস পাঠানোর');
        $sms->setBusinessType(BusinessType::SMANAGER)->setFeatureType(FeatureType::DUE_TRACKER);
        if(config('sms.is_on')) $sms->shoot();
        $transaction = (new WalletTransactionHandler())
            ->setModel($request->partner)
            ->setAmount($sms_cost)
            ->setType(Types::debit())
            ->setLog($sms_cost . $log)
            ->setTransactionDetails([])
            ->setSource(TransactionSources::SMS)
            ->store();
        $this->storeJournal($request->partner, $transaction);
        return true;
    }

    private function storeJournal($partner,  $transaction) {
        (new JournalCreateRepository())->setTypeId($partner->id)
            ->setSource($transaction)
            ->setAmount($transaction->amount)
            ->setDebitAccountKey(SmsPurchase::SMS_PURCHASE_FROM_SHEBA)
            ->setCreditAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
            ->setDetails("Due tracker sms sent charge")
            ->setReference("")
            ->store();
    }

    public function getSms($data)
    {
        $log = " BDT has been deducted for sending ";
        $message_data = [
            'customer_name' => $data['customer_name'],
            'partner_name'  => $data['partner_name'],
            'amount'        => $data['amount'],
            'company_number'=> $data['company_number']
        ];

        if ($data['type'] == 'due') {
            $sms = (new SmsHandlerRepo('inform-due'));
            $message_data['payment_link']  = $data['payment_link'];
            $log = "due details";
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
                'question' => 'বাকির খাতা কি?',
                'answer'   => 'বাকির খাতা হচ্ছে বাকি/জমার হিসেব রাখার ডিজিটাল প্রসেস। এখানে আপনি বাকি এবং জমার হিসেব রাখাতে পারবেন খুব সহজে।'
            ],
            [
                'question' => 'কিভাবে বাকির খাতা ব্যবহার করব?',
                'answer'   => 'আপনি বাকির খাতায় গিয়ে কাস্টমার যোগ করে অথবা কাস্টমার লিস্ট থেকে কাস্টমার সিলেক্ট করে বাকি/জমার এন্টি দিতে পারবেন। আপনি বাকি/জমার টাকার পরিমান, নোট,তারিখ এবং ছবি যোগ করার মাধ্যমে এন্ট্রি যোগ করতে পারবেন।'
            ],
            [
                'question' => 'মোট বাকি কি?',
                'answer'   => 'কাস্টমার এর কাছ থেকে মোট বাকির পরিমান।'
            ],
            [
                'question' => 'মোট জমা কি?',
                'answer'   => 'কাস্টমার এর কাছ থেকে মোট জমার পরিমান।'
            ],
            [
                'question' => 'বাকির রিমাইন্ডার কি?',
                'answer'   => 'বাকির রিমাইন্ডার থেকে কাস্টমার আপনাকে কবে বাকি পরিশোধ করবে তা দেখতে পারবেন।'
            ],
            [
                'question' => 'POS থেকে বাকিতে সেল করলে সেটা বাকির খাতায় আসবে কি?',
                'answer'   => 'হ্যাঁ আসবে।'
            ]
        ];
    }

    /**
     * @param Request $request
     * @param PaymentLinkCreator $paymentLinkCreator
     * @return mixed
     * @throws \Exception
     */
    public function createPaymentLink(Request $request, $paymentLinkCreator )
    {
        $purpose = 'Due Collection';
        $customer = PosCustomer::find($request->customer_id);
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

        throw new \Exception('payment link creation fail');
    }
}
