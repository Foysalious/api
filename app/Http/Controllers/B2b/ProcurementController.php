<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Bid;
use App\Models\Business;
use App\Models\Partner;
use App\Models\Procurement;
use App\Sheba\Bitly\BitlyLinkShort;
use App\Sheba\Business\ACL\AccessControl;
use App\Transformers\AttachmentTransformer;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Sheba\Business\Procurement\Creator;
use Sheba\Business\Procurement\WorkOrderDataGenerator;
use Sheba\Dal\ProcurementInvitation\Model as ProcurementInvitation;
use Sheba\Dal\ProcurementInvitation\ProcurementInvitationRepositoryInterface;
use Sheba\Logs\ErrorLog;
use Sheba\ModificationFields;
use Sheba\Payment\Adapters\Payable\ProcurementAdapter;
use Sheba\Payment\AvailableMethods;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\PaymentManager;
use Sheba\Payment\ShebaPaymentValidator;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Sms\Sms;
use Sheba\Business\ProcurementInvitation\Creator as ProcurementInvitationCreator;
use Throwable;

class ProcurementController extends Controller
{
    use ModificationFields;

    public function store(Request $request, AccessControl $access_control, Creator $creator)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string', 'number_of_participants' => 'required|numeric', 'last_date_of_submission' => 'required|date_format:Y-m-d', 'procurement_start_date' => 'required|date_format:Y-m-d', 'procurement_end_date' => 'required|date_format:Y-m-d', 'payment_options' => 'required|string', 'type' => 'required|string:in:basic,advanced', 'items' => 'sometimes|required|string', 'description' => 'sometimes|required|string', 'is_published' => 'sometimes|required|integer', 'attachments.*' => 'file'
            ]);
            if (!$access_control->setBusinessMember($request->business_member)->hasAccess('procurement.rw')) return api_response($request, null, 403);

            $this->setModifier($request->manager_member);

            $creator->setType($request->type)->setOwner($request->business)->setTitle($request->title)->setPurchaseRequest($request->purchase_request_id)->setLongDescription($request->description)->setOrderStartDate($request->order_start_date)->setOrderEndDate($request->order_end_date)->setInterviewDate($request->interview_date)->setProcurementStartDate($request->procurement_start_date)->setProcurementEndDate($request->procurement_end_date)->setItems($request->items)->setQuestions($request->questions)->setNumberOfParticipants($request->number_of_participants)->setLastDateOfSubmission($request->last_date_of_submission)->setPaymentOptions($request->payment_options)->setIsPublished($request->is_published)->setLabels($request->labels)->setCreatedBy($request->manager_member);

            if ($request->attachments && is_array($request->attachments)) $creator->setAttachments($request->attachments);

            $procurement = $creator->create();

            return api_response($request, $procurement, 200, ['id' => $procurement->id]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function index($business, Request $request, AccessControl $access_control, ProcurementRepositoryInterface $procurement_repository)
    {
        try {
            $this->validate($request, [
                'status' => 'sometimes|string',
            ]);
            $access_control->setBusinessMember($request->business_member);
            if (!($access_control->hasAccess('procurement.r') || $access_control->hasAccess('procurement.rw'))) return api_response($request, null, 403);
            $this->setModifier($request->manager_member);
            $business = $request->business;
            list($offset, $limit) = calculatePagination($request);
            $procurements = $procurement_repository->ofBusiness($business->id)->select(['id', 'title', 'status', 'last_date_of_submission', 'created_at', 'is_published'])->orderBy('id', 'desc');
            $total_procurement = $procurements->get()->count();

            if ($request->has('status') && $request->status != 'all') {
                if ($request->status === 'drafted') {
                    $procurements->where('is_published', 0);
                } else {
                    $procurements->where('status', $request->status);
                }
            }

            $start_date = $request->has('start_date') ? $request->start_date : null;
            $end_date = $request->has('end_date') ? $request->end_date : null;
            if ($start_date && $end_date) {
                $procurements->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }
            $procurements = $procurements->skip($offset)->limit($limit);
            $procurements_list = [];
            foreach ($procurements->get() as $procurement) {
                array_push($procurements_list, [
                    "id" => $procurement->id, "title" => $procurement->title, "status" => $procurement->status, "is_published" => $procurement->is_published, "last_date_of_submission" => Carbon::parse($procurement->last_date_of_submission)->format('d/m/y'), "bid_count" => $procurement->bids()->where('status', '<>', 'pending')->get()->count()
                ]);
            }
            if (count($procurements_list) > 0) return api_response($request, $procurements_list, 200, [
                'procurements' => $procurements_list, 'total_procurement' => $total_procurement
            ]); else return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $procurement = Procurement::find($request->procurement);

            if (is_null($procurement)) {
                return api_response($request, null, 404, ["message" => "Not found."]);
            } else {
                $price_quotation = $procurement->items->where('type', 'price_quotation')->first();
                $technical_evaluation = $procurement->items->where('type', 'technical_evaluation')->first();
                $company_evaluation = $procurement->items->where('type', 'company_evaluation')->first();

                $procurement_details = [
                    'id' => $procurement->id, 'title' => $procurement->title, 'status' => $procurement->status, 'long_description' => $procurement->long_description, 'labels' => $procurement->getTagNamesAttribute()->toArray(), 'start_date' => Carbon::parse($procurement->procurement_start_date)->format('d/m/y'), 'published_at' => $procurement->is_published ? Carbon::parse($procurement->published_at)->format('d/m/y') : null, 'end_date' => Carbon::parse($procurement->procurement_end_date)->format('d/m/y'), 'number_of_participants' => $procurement->number_of_participants, 'last_date_of_submission' => Carbon::parse($procurement->last_date_of_submission)->format('Y-m-d'), 'payment_options' => $procurement->payment_options, 'created_at' => Carbon::parse($procurement->created_at)->format('d/m/y'), 'price_quotation' => $price_quotation ? $price_quotation->fields ? $price_quotation->fields->toArray() : null : null, 'technical_evaluation' => $technical_evaluation ? $technical_evaluation->fields ? $technical_evaluation->fields->toArray() : null : null, 'company_evaluation' => $company_evaluation ? $company_evaluation->fields ? $company_evaluation->fields->toArray() : null : null, 'attachments' => $procurement->attachments->map(function (Attachment $attachment) {
                        return (new AttachmentTransformer())->transform($attachment);
                    })->toArray()
                ];
                return api_response($request, $procurement_details, 200, ['procurements' => $procurement_details]);
            }
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function updateGeneral(Request $request)
    {
        try {
            $this->validate($request, [
                'number_of_participants' => 'required|numeric', 'last_date_of_submission' => 'required|date_format:Y-m-d', 'procurement_start_date' => 'date_format:Y-m-d', 'payment_options' => 'string'
            ]);

            $procurement = Procurement::find($request->procurement);

            if (is_null($procurement)) {
                return api_response($request, null, 404, ["message" => "Not found."]);
            } else {
                $procurement->number_of_participants = $request->number_of_participants;
                $procurement->last_date_of_submission = $request->last_date_of_submission;
                if ($request->procurement_start_date) $procurement->procurement_start_date = $request->procurement_start_date;
                if ($request->payment_options) $procurement->payment_options = $request->payment_options;

                $procurement->save();
                return api_response($request, null, 200, ["message" => "Successful"]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param Sms $sms
     * @param ErrorLog $errorLog
     * @param BitlyLinkShort $bitly_link
     * @param ProcurementRepositoryInterface $procurementRepository
     * @param ProcurementInvitationRepositoryInterface $procurement_invitation_repository
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @return bool|JsonResponse
     */
    public function sendInvitation($business, $procurement, Request $request, Sms $sms,
                                   ErrorLog $errorLog,
                                   BitlyLinkShort $bitly_link,
                                   ProcurementRepositoryInterface $procurementRepository,
                                   ProcurementInvitationRepositoryInterface $procurement_invitation_repository,
                                   BusinessMemberRepositoryInterface $business_member_repository)
    {
        try {
            $this->validate($request, [
                'partners' => 'required|string',
            ]);
            $partners = Partner::whereIn('id', json_decode($request->partners))->get();
            $business = $request->business;
            $procurement = $procurementRepository->find($procurement);

            foreach ($partners as $partner) {
                /** @var Partner $partner */
                $creator = new ProcurementInvitationCreator($procurement_invitation_repository);
                $procurement_invitation = $creator->setBusinessMember($request->business_member)->setProcurement($procurement)->setPartner($partner);
                if ($creator->hasError()) {
                    if ($creator->getErrorCode() == 409) {
                        $procurement_invitation = $procurement_invitation->getProcurementInvitation();
                        $this->shootSmsForInvitation($business, $procurement_invitation, $bitly_link, $sms, $partner);
                    }
                    continue;
                }

                $procurement_invitation = $procurement_invitation->create();
                $this->shootSmsForInvitation($business, $procurement_invitation, $bitly_link, $sms, $partner);
            }

            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function updateStatus($business, $procurement, Request $request, Creator $creator)
    {
        try {
            $this->validate($request, [
                'is_published' => 'required|integer:in:1,0',
            ]);
            $procurement = Procurement::find((int)$procurement);
            if (!$procurement) {
                return api_response($request, null, 404);
            } else {
                $creator->setIsPublished($request->is_published)->changeStatus($procurement);
                return api_response($request, null, 200);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param ProcurementAdapter $procurement_adapter
     * @param PaymentManager $payment_manager
     * @param ShebaPaymentValidator $payment_validator
     * @param ProcurementRepositoryInterface $procurement_repository
     * @return JsonResponse
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     */
    public function clearBills($business, $procurement, Request $request, ProcurementAdapter $procurement_adapter, PaymentManager $payment_manager, ShebaPaymentValidator $payment_validator, ProcurementRepositoryInterface $procurement_repository)
    {
        $this->validate($request, [
            'payment_method' => 'required|in:' . implode(',', AvailableMethods::getBusinessPayments()),
            'emi_month' => 'numeric'
        ]);
        $payment_method = $request->payment_method;
        $procurement = $procurement_repository->find($procurement);
        $payment_validator->setPayableType('procurement')->setPayableTypeId($procurement->id)->setPaymentMethod($payment_method);
        if (!$payment_validator->canInitiatePayment()) return api_response($request, null, 403, ['message' => "Can't send multiple requests within 1 minute."]);
        $payable = $procurement_adapter->setModelForPayable($procurement)->setEmiMonth($request->emi_month)->getPayable();
        $payment = $payment_manager->setMethodName($payment_method)->setPayable($payable)->init();
        return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);
    }

    public function orderTimeline($business, $procurement, Request $request, Creator $creator)
    {
        try {
            $procurement = $creator->getProcurement($procurement)->getBid();
            $order_timelines = $creator->formatTimeline();
            return api_response($request, $order_timelines, 200, ['timelines' => $order_timelines]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function procurementOrders($business, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $procurements = Procurement::order()->with([
                'bids' => function ($q) {
                    $q->select('id', 'procurement_id', 'bidder_id', 'bidder_type', 'status', 'price');
                }
            ])->where('owner_id', (int)$business)->orderBy('id', 'DESC');

            $total_procurement = $procurements->get()->count();

            if ($request->has('status') && $request->status != 'all') {
                $procurements = $procurements->where('status', $request->status);
            }

            $start_date = $request->has('start_date') ? $request->start_date : null;
            $end_date = $request->has('end_date') ? $request->end_date : null;
            if ($start_date && $end_date) {
                $procurements->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }
            $procurements = $procurements->skip($offset)->limit($limit)->get();
            $rfq_order_lists = [];
            foreach ($procurements as $procurement) {
                $bid = $procurement->getActiveBid() ? $procurement->getActiveBid() : null;
                array_push($rfq_order_lists, [
                    'procurement_id' => $procurement->id, 'procurement_title' => $procurement->title, 'procurement_status' => $procurement->status, 'created_at' => $procurement->created_at->format('d/m/y'), 'color' => constants('PROCUREMENT_ORDER_STATUSES_COLOR')[$procurement->status], 'bid_id' => $bid ? $bid->id : null, 'price' => $bid ? $bid->price : null, 'vendor' => [
                        'name' => $bid ? $bid->bidder->name : null
                    ]
                ]);
            }

            return api_response($request, $rfq_order_lists, 200, [
                'rfq_order_lists' => $rfq_order_lists, 'total_procurement' => $total_procurement,
            ]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param $procurement
     * @param $bid
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function showProcurementOrder($business, $procurement, $bid, Request $request, Creator $creator)
    {
        try {
            $bid = Bid::findOrFail((int)$bid);
            $rfq_order_details = $creator->getProcurement($procurement)->setBid($bid)->formatData();
            return api_response($request, null, 200, ['order_details' => $rfq_order_details]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function orderBill($business, $procurement, Request $request, Creator $creator)
    {
        try {
            $procurement = Procurement::findOrFail((int)$procurement);
            $procurement->calculate();
            $rfq_order_bill['total_price'] = $procurement->getActiveBid()->price;
            $rfq_order_bill['paid'] = $procurement->paid;
            $rfq_order_bill['due'] = $procurement->due;
            return api_response($request, $rfq_order_bill, 200, ['rfq_order_bill' => $rfq_order_bill]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function downloadPdf(Request $request)
    {
        $procurement = Procurement::find($request->procurement);
        $price_quotation = $procurement->items->where('type', 'price_quotation')->first();
        $technical_evaluation = $procurement->items->where('type', 'technical_evaluation')->first();
        $company_evaluation = $procurement->items->where('type', 'company_evaluation')->first();

        $procurement_details = [
            'id' => $procurement->id,
            'title' => $procurement->title,
            'status' => $procurement->status,
            'long_description' => $procurement->long_description,
            'labels' => $procurement->getTagNamesAttribute()->toArray(),
            'start_date' => Carbon::parse($procurement->procurement_start_date)->format('d/m/y'),
            'published_at' => $procurement->is_published ? Carbon::parse($procurement->published_at)->format('d/m/y') : null,
            'end_date' => Carbon::parse($procurement->procurement_end_date)->format('d/m/y'),
            'number_of_participants' => $procurement->number_of_participants,
            'last_date_of_submission' => Carbon::parse($procurement->last_date_of_submission)->format('Y-m-d'),
            'payment_options' => $procurement->payment_options,
            'created_at' => Carbon::parse($procurement->created_at)->format('d/m/y'),
            'price_quotation' => $price_quotation ? $price_quotation->fields ? $price_quotation->fields->toArray() : null : null,
            'technical_evaluation' => $technical_evaluation ? $technical_evaluation->fields ? $technical_evaluation->fields : null : null,
            'company_evaluation' => $company_evaluation ? $company_evaluation->fields ? $company_evaluation->fields : null : null,
        ];

        return App::make('dompdf.wrapper')
            ->loadView('pdfs.procurement_details', compact('procurement_details'))
            ->download("procurement_details.pdf");
    }

    /**
     * @param $business
     * @param $procurement
     * @param $bid
     * @param Request $request
     * @param WorkOrderDataGenerator $data_generator
     * @return JsonResponse
     */
    public function workOrder($business, $procurement, $bid, Request $request, WorkOrderDataGenerator $data_generator)
    {
        try {
            $business = $request->business;
            $bid = Bid::findOrFail((int)$bid);
            $work_order = $data_generator->setBusiness($business)->setProcurement($procurement)->setBid($bid)->get();
            return api_response($request, null, 200, ['work_order' => $work_order]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param $procurement
     * @param $bid
     * @param Request $request
     * @param WorkOrderDataGenerator $data_generator
     * @return JsonResponse
     */
    public function downloadWorkOrder($business, $procurement, $bid, Request $request, WorkOrderDataGenerator $data_generator)
    {
        $business = $request->business;
        $bid = Bid::findOrFail((int)$bid);
        $work_order = $data_generator->setBusiness($business)->setProcurement($procurement)->setBid($bid)->get();

        return App::make('dompdf.wrapper')
            ->loadView('pdfs.work_order', compact('work_order'))
            ->download('work_order.pdf');

    }

    /**
     * @param Business $business
     * @param ProcurementInvitation $procurement_invitation
     * @param BitlyLinkShort $bitly_link
     * @param Sms $sms
     * @param Partner $partner
     */
    private function shootSmsForInvitation(Business $business, ProcurementInvitation $procurement_invitation, BitlyLinkShort $bitly_link, Sms $sms, Partner $partner)
    {
        $url = config('sheba.partners_url') . "/v3/rfq-invitations/$procurement_invitation->id";
        $sms->shoot($partner->getManagerMobile(), "You have been invited to serve $business->name. Now go to this link-" . $bitly_link->shortUrl($url));
    }
}
