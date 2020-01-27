<?php

namespace Sheba\DueTracker;

use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\Profile;
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
     * @return array
     * @throws InvalidPartnerPosCustomer
     * @throws ExpenseTrackingServerError
     */
    public function store(Partner $partner, Request $request)
    {

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

    private function createStoreData(Request $request)
    {
        $data['created_from']   = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount']         = (double)$request->amount;
        $data['note']           = $request->note;
        $data['amount_cleared'] = $request->type == "due" ? 0 : (double)$request['amount'];
        $data['head_name']      = AutomaticIncomes::DUE_TRACKER;
        $data['created_at']     = $request->created_at ?: Carbon::now()->format('Y-m-d H:s:i');
        $data['attachments']    = $this->uploadAttachments($request);
        return $data;
    }

    private function uploadAttachments(Request $request)
    {
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $key => $file) {
                list($file, $filename) = $this->makeAttachment($file, '_attachments');
                $attachments[] = $this->saveFileToCDN($file, getDueTrackerAttachmentsFolder(), $filename);;
            }
        }
        return json_encode($attachments);
    }
    public function generateDueReminders(array $list,Partner $partner){
        $response['today'] = [];
        $response['previous'] = [];
        $response['next'] = [];
        foreach ($list['list'] as $item) {
            $partner_pos_customer = PartnerPosCustomer::byPartnerAndCustomer($partner->id, $item['customer_id'])->first();
            $due_date_reminder = $partner_pos_customer['due_date_reminder'];

            if ($partner_pos_customer && $due_date_reminder) {
                $temp['customer_name'] = $item['customer_name'];
                $temp['customer_id'] = $item['customer_id'];
                $temp['profile_id'] = $item['profile_id'];
                $temp['phone'] = $partner_pos_customer->details()['phone'];
                $temp['balance'] = $item['balance'];
                $temp['due_date_reminder'] = $due_date_reminder;


                if (Carbon::parse($due_date_reminder)->format('d-m-Y') == Carbon::parse(Carbon::today())->format('d-m-Y')) {
                    array_push($response['today'], $temp);
                } else if (Carbon::parse($due_date_reminder)->format('d-m-Y') < Carbon::parse(Carbon::today())->format('d-m-Y')) {
                    array_push($response['previous'], $temp);
                } else if (Carbon::parse($due_date_reminder)->format('d-m-Y') > Carbon::parse(Carbon::today())->format('d-m-Y')) {
                    array_push($response['next'], $temp);
                }
            }

        }
        return $response;
    }

    public function generateDueCalender(array $dueList,Request $request){
        $calender = [];

        foreach ($dueList['list'] as $item) {
            $item['customer_id'] = 271;
            $partner_pos_customer = PartnerPosCustomer::byPartnerAndCustomer($request->partner->id, $item['customer_id'])->first();
            $due_date_reminder = $partner_pos_customer['due_date_reminder'];
            if ($partner_pos_customer && $due_date_reminder) {
                $year = Carbon::parse($due_date_reminder)->year;
                $month = Carbon::parse($due_date_reminder)->month;
                $day = Carbon::parse($due_date_reminder)->day;
                if($year == $request->year && $month == $request->month) {
                    if (!isset($calender[$day])) $calender[$day] = [];
                    array_push($calender[$day], $item);
                }
            }
        }
        $response = [];
        foreach($calender as $key => $items){
            $data['date'] = Carbon::create($request->year,$request->month,$key)->format('d-m-Y');
            $data['count'] = count($items);
            $data['customers'] = [];
            foreach($items as $item){
                $temp['customer_name'] = $item['customer_name'];
                $temp['customer_id'] = $item['customer_id'];
                $temp['profile_id'] = $item['profile_id'];
                $temp['balance'] = $item['balance'];
                array_push($data['customers'],$temp);
            }
            array_push($response,$data);
        }
        return $response;
    }
}
