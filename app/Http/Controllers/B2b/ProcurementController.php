<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Procurement;
use App\Sheba\Business\ACL\AccessControl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Business\Procurement\Creator;
use Sheba\Logs\ErrorLog;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Sms\Sms;

class ProcurementController extends Controller
{
    use ModificationFields;

    public function store(Request $request, AccessControl $access_control, Creator $creator)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'number_of_participants' => 'required|numeric',
                'last_date_of_submission' => 'required|date_format:Y-m-d',
                'procurement_start_date' => 'required|date_format:Y-m-d',
                'payment_options' => 'required|string',
                'type' => 'required|string:in:basic,advanced,product,service',
                'items' => 'sometimes|string',
                'is_published' => 'sometimes|integer',
                /*'description' => 'required',
                 'estimated_price' => 'required|string',
                 'purchase_request_id' => 'sometimes|numeric',
                 'questions' => 'required|string',
                 'order_start_date' => 'sometimes|date_format:Y-m-d',
                 'order_end_date' => 'sometimes|date_format:Y-m-d',
                 'interview_date' => 'sometimes|date_format:Y-m-d',
                 'tender_start_date' => 'sometimes|date_format:Y-m-d',
                 'tender_end_date' => 'sometimes|date_format:Y-m-d',*/
            ]);
            if (!$access_control->setBusinessMember($request->business_member)->hasAccess('procurement.rw')) return api_response($request, null, 403);
            $this->setModifier($request->manager_member);

            $creator->setType($request->type)->setOwner($request->business)->setTitle($request->title)->setPurchaseRequest($request->purchase_request_id)
                ->setLongDescription($request->description)->setOrderStartDate($request->order_start_date)->setOrderEndDate($request->order_end_date)
                ->setInterviewDate($request->interview_date)->setProcurementStartDate($request->procurement_start_date)->setProcurementEndDate($request->tender_start_date)
                ->setItems($request->items)->setQuestions($request->questions)->setNumberOfParticipants($request->number_of_participants)
                ->setLastDateOfSubmission($request->last_date_of_submission)->setPaymentOptions($request->payment_options)->setIsPublished($request->is_published)
                ->setLabels($request->labels);
            $procurement = $creator->create();
            return api_response($request, $procurement, 200, ['id' => $procurement->id]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
            $procurements = $procurement_repository->ofBusiness($business->id)->select(['id', 'title', 'status', 'last_date_of_submission', 'created_at'])->orderBy('id', 'desc');
            $total_procurement = $procurements->get()->count();
            $procurements = $procurements->skip($offset)->limit($limit);

            if ($request->has('status') && $request->status != 'all') {
                $procurements->where('status', $request->status);
            }

            $start_date = $request->has('start_date') ? $request->start_date : null;
            $end_date = $request->has('end_date') ? $request->end_date : null;
            if ($start_date && $end_date) {
                $procurements->whereBetween('published_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }

            $procurements_list = [];
            foreach ($procurements->get() as $procurement) {
                array_push($procurements_list, [
                    "id" => $procurement->id,
                    "title" => $procurement->title,
                    "status" => $procurement->status,
                    "last_date_of_submission" => Carbon::parse($procurement->last_date_of_submission)->format('d/m/y'),
                    "bid_count" => 0
                ]);
            }
            if (count($procurements_list) > 0) return api_response($request, $procurements_list, 200, [
                'procurements' => $procurements_list,
                'total_procurement' => $total_procurement
            ]);
            else return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
                    'id' => $procurement->id,
                    'title' => $procurement->title,
                    'status' => $procurement->status,
                    'labels' => $procurement->getTagNamesAttribute()->toArray(),
                    'start_date' => $procurement->procurement_start_date,
                    'end_date' => $procurement->procurement_end_date,
                    'number_of_participants' => $procurement->number_of_participants,
                    'last_date_of_submission' => $procurement->last_date_of_submission,
                    'payment_options' => $procurement->payment_options,
                    'created_at' => $procurement->created_at->toDateString(),
                    'price_quotation' => $price_quotation ? $price_quotation->fields ? $price_quotation->fields->toArray() : null : null,
                    'technical_evaluation' => $technical_evaluation ? $technical_evaluation->fields ? $technical_evaluation->fields->toArray() : null : null,
                    'company_evaluation' => $company_evaluation ? $company_evaluation->fields ? $company_evaluation->fields->toArray() : null : null,
                ];
                return api_response($request, $procurement_details, 200, ['procurements' => $procurement_details]);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateGeneral(Request $request)
    {
        try {
            $this->validate($request, [
                'number_of_participants' => 'required|numeric',
                'last_date_of_submission' => 'required|date_format:Y-m-d',
                'procurement_start_date' => 'required|date_format:Y-m-d',
                'payment_options' => 'required|string'
            ]);

            $procurement = Procurement::find($request->procurement);

            if (is_null($procurement)) {
                return api_response($request, null, 404, ["message" => "Not found."]);
            } else {
                $procurement->number_of_participants = $request->number_of_participants;
                $procurement->last_date_of_submission = $request->last_date_of_submission;
                $procurement->procurement_start_date = $request->procurement_start_date;
                $procurement->payment_options = $request->payment_options;

                $procurement->save();
                return api_response($request, null,200, ["message" => "Successful"]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e->getMessage());
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function sendInvitation($procurement, Request $request, Sms $sms, ErrorLog $errorLog)
    {
        try {
            $this->validate($request, [
                'partners' => 'required|string',
            ]);
            $partners = Partner::whereIn('id', json_decode($request->partners))->get();
            $business = $request->business;
            foreach ($partners as $partner) {
                /** @var Partner $partner */
                $sms->shoot($partner->getManagerMobile(), "You have been invited to serve" . $business->name);
            }
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $errorLog->setException($e)->setRequest($request)->setErrorMessage($message)->send();
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            $errorLog->setException($e)->send();
            return api_response($request, null, 500);
        }
    }
}