<?php

namespace Sheba\DueTracker;

use App\Jobs\PartnerRenewalSMS;
use App\Jobs\SendToCustomerToInformDueDepositSMS;
use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\Profile;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\BaseRepository;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class DueTrackerRepository extends BaseRepository {
    use ModificationFields, CdnFileManager, FileManager;

    public function getDueList(Request $request, $paginate = true) {
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
        if ($request->has('q') && !empty($request->q)) {
            $query = preg_replace("/\+/", "", $request->q);
            $list  = $list->filter(function ($item) use ($query) {
                return preg_match("%$query%i", $item['customer_name']) || preg_match("/$query/", $item['customer_mobile']);
            })->values();
        }
        if (!empty($order_by) && $order_by == "name") {
            $order = ($request->order == 'desc') ? 'sortByDesc' : 'sortBy';
            $list  = $list->$order('customer_name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        }
        $total = $list->count();
        if ($paginate) {
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

    private function updateRequestParam(Request $request, $url) {
        $order_by = $request->order_by;
        if (!empty($order_by) && $order_by != "name") {
            $order = !empty($request->order) ? strtolower($request->order) : 'desc';
            $url   .= "&order_by=$order_by&order=$order";
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $url .= "&start=$request->start_date&end=$request->end_date";
        }
        return $url;
    }

    private function attachProfile(Collection $list) {
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
    public function getDueListByProfile(Partner $partner, Request $request) {
        $partner_pos_customer = PartnerPosCustomer::byPartner($partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if (empty($partner_pos_customer))
            throw new InvalidPartnerPosCustomer();
        /** @var PosCustomer $customer */
        $customer = $partner_pos_customer->customer;
        $url      = "accounts/$this->accountId/entries/due-list/$customer->profile_id?";
        $url      = $this->updateRequestParam($request, $url);
        $result   = $this->client->get($url);
        $list     = collect($result['data']['list'])->map(function ($item) {
            $item['created_at'] = Carbon::parse($item['created_at'])->format('Y-m-d h:i A');
            $item['entry_at']   = Carbon::parse($item['entry_at'])->format('Y-m-d h:i A');
            return $item;
        });
        list($offset, $limit) = calculatePagination($request);
        $list               = $list->slice($offset)->take($limit);
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
                'name'              => $customer->profile->name,
                'mobile'            => $customer->profile->mobile,
                'avatar'            => $customer->profile->pro_pic,
                'due_date_reminder' => $partner_pos_customer->due_date_reminder
            ],
            'partner'    => $this->getPartnerInfo($partner),
            'other_info' => [
                'total_transactions' => $total_transactions,
                'total_credit'       => $total_credit,
                'total_debit'        => $total_debit,
            ]
        ];
    }

    /**
     * @param $partner
     * @return array
     */
    private function getPartnerInfo($partner) {
        return [
            'name'   => $partner->name,
            'avatar' => $partner->logo,
            'mobile' => $partner->mobile,
        ];
    }


    /**
     * @param Partner $partner
     * @param Request $request
     * @return array
     * @throws InvalidPartnerPosCustomer
     * @throws ExpenseTrackingServerError
     */
    public function store(Partner $partner, Request $request) {

        $partner_pos_customer = PartnerPosCustomer::byPartner($partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if (empty($partner_pos_customer))
            throw new InvalidPartnerPosCustomer();
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
     * @throws InvalidPartnerPosCustomer
     */
    public function update(Partner $partner, Request $request) {
        $partner_pos_customer = PartnerPosCustomer::byPartner($partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if (empty($partner_pos_customer))
            throw new InvalidPartnerPosCustomer();
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

    public function createPosOrderPayment($amount_cleared, $pos_order_id, $payment_method) {
        $payment_data['pos_order_id'] = $pos_order_id;
        $payment_data['amount']       = $amount_cleared;
        $payment_data['method']       = $payment_method;
        $this->paymentCreator->credit($payment_data);
    }

    private function createStoreData(Request $request) {
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

    private function uploadAttachments(Request $request) {
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
    private function updateAttachments(Request $request) {
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
    private function deleteFromCDN($files) {
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
    public function generateDueReminders(array $list, Partner $partner) {
        $response['today']    = [];
        $response['previous'] = [];
        $response['next']     = [];
        foreach ($list['list'] as $item) {
            $partner_pos_customer = PartnerPosCustomer::byPartnerAndCustomer($partner->id, $item['customer_id'])->first();
            $due_date_reminder    = $partner_pos_customer['due_date_reminder'];
            if ($partner_pos_customer && $due_date_reminder) {
                $temp['customer_name']     = $item['customer_name'];
                $temp['customer_id']       = $item['customer_id'];
                $temp['profile_id']        = $item['profile_id'];
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
    public function generateDueCalender(array $dueList, Request $request) {
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
    public function removeEntry($entry_id) {
        return $this->client->delete("accounts/$this->accountId/entries/$entry_id");
    }

    /**
     * @param $profile_id
     * @throws ExpenseTrackingServerError
     */
    public function removeCustomer($profile_id) {
        $url = "accounts/$this->accountId/remove/$profile_id";
        $this->client->delete($url);

    }


    /**
     * @param Request $request
     * @return mixed
     * @throws InvalidPartnerPosCustomer
     */
    public function sendSMS(Request $request) {
        $partner_pos_customer = PartnerPosCustomer::byPartner($request->partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if (empty($partner_pos_customer))
            throw new InvalidPartnerPosCustomer();
        /** @var PosCustomer $customer */
        $customer = $partner_pos_customer->customer;
        $data     = [
            'type'          => $request->type,
            'partner_name'  => $request->partner->name,
            'customer_name' => $customer->profile->name,
            'mobile'        => $customer->profile->mobile,
            'amount'        => $request->amount,
        ];
        if ($request->type == 'due') {
            $data['payment_link'] = $request->payment_link;
        }
        return dispatch((new SendToCustomerToInformDueDepositSMS($request->partner, $data)));
    }

    /**
     * @return array
     */
    public function getFaqs() {
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

}
