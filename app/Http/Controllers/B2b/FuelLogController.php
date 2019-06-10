<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\FuelLog;
use App\Repositories\CommentRepository;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Attachments\FilesAttachment;
use Sheba\Business\FuelLog\Creator;
use Sheba\ModificationFields;
use DB;

class FuelLogController extends Controller
{
    use ModificationFields;
    use FilesAttachment;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            list($offset, $limit) = calculatePagination($request);
            $fuel_logs = FuelLog::with('vehicle')->orderBy('id', 'DESC')->skip($offset)->limit($limit);

            if ($request->has('type')) {
                $fuel_logs = $fuel_logs->whereHas('vehicle', function ($query) use ($request) {
                    $query->whereHas('basicInformations', function ($query) use ($request) {
                        $query->where('type', $request->type);
                    });
                });
            }

            $logs_lists = [];
            foreach ($fuel_logs->get() as $log) {
                $vehicle = $log->vehicle;
                $basic_information = $vehicle->basicInformations;
                $logs = [
                    'id' => $log->id,
                    'type' => $log->type,
                    'unit' => $log->unit,
                    'volume' => $log->volume,
                    'price' => $log->price,
                    'refilled_date' => $log->refilled_date,
                    'station_name' => $log->station_name,
                    'station_address' => $log->station_address,
                    'reference' => $log->reference,
                    'vehicle' => [
                        'id' => $vehicle->id,
                        'vehicle_model' => $basic_information->model_name,
                        'model_year' => Carbon::parse($basic_information->model_year)->format('Y'),
                        'status' => $vehicle->status,
                        'vehicle_type' => $basic_information->type,
                        'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    ],
                ];
                array_push($logs_lists, $logs);
            }
            if (count($logs_lists) > 0) return api_response($request, $logs_lists, 200, [
                'logs_lists' => $logs_lists,
                'total_fuel_cost' => '793.53',
                'total_gallons' => '311',
                'cost_per_gallon' => '240',
            ]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($business, $log, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $fuel_log = FuelLog::find((int)$log);
            if (!$fuel_log) return api_response($request, null, 404);

            $vehicle = $fuel_log->vehicle;
            $basic_information = $vehicle->basicInformations;
            $fuel_logs = [
                'id' => $fuel_log->id,
                'type' => $fuel_log->type,
                'unit' => $fuel_log->unit,
                'volume' => $fuel_log->volume,
                'price' => $fuel_log->price,
                'refilled_date' => $fuel_log->refilled_date,
                'station_name' => $fuel_log->station_name,
                'station_address' => $fuel_log->station_address,
                'reference' => $fuel_log->reference,
                'vehicle' => [
                    'id' => $vehicle->id,
                    'vehicle_model' => $basic_information->model_name,
                    'model_year' => Carbon::parse($basic_information->model_year)->format('Y'),
                    'status' => $vehicle->status,
                    'vehicle_type' => $basic_information->type,
                    'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                ],
            ];

            if (count($fuel_logs) > 0) return api_response($request, $fuel_logs, 200, ['fuel_logs' => $fuel_logs]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request, Creator $creator)
    {
        try {
            $this->validate($request, [
                'vehicle_id' => 'required|numeric',
                'date' => 'required|date',
                'price' => 'required|numeric',
                'volume' => 'required|numeric',
                'type' => 'required|string|in:' . implode(',', constants('FUEL_TYPES')),
                'unit' => 'required|string|in:' . implode(',', constants('FUEL_UNITS')),
                'station_name' => 'string',
                'station_address' => 'string',
                'reference' => 'string',
                'comment' => 'string'
            ]);
            $member = $request->manager_member;
            $this->setModifier($request->manager_member);
            $creator->setVehicleId($request->vehicle_id)->setDate($request->date)
                ->setPrice($request->price)->setVolume($request->volume)->setUnit($request->unit)->setType($request->type)
                ->setStationName($request->station_name)->setStationAddress($request->station_address)
                ->setReference($request->reference);
            $fuel_log = null;
            DB::transaction(function () use (&$fuel_log, $creator, $member, $request) {
                $fuel_log = $creator->save();
                if ($request->comment) (new CommentRepository('FuelLog', $fuel_log->id, $member))->store($request->comment);
                foreach ($request->file as $file) {
                    $data = $this->storeAttachmentToCDN($file);
                    $attachment = $fuel_log->attachments()->save(new Attachment($this->withBothModificationFields($data)));
                }
            });
            return api_response($request, null, 200, ['id' => $fuel_log->id]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAttachments($business, $log, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $fuel_log = FuelLog::find((int)$log);
            if (!$fuel_log) return api_response($request, null, 404);
            list($offset, $limit) = calculatePagination($request);
            $attaches = Attachment::where('attachable_type', get_class($fuel_log))->where('attachable_id', $fuel_log->id)
                ->select('id', 'title', 'file', 'file_type')->orderBy('id', 'DESC')->skip($offset)->limit($limit)->get();
            $attach_lists = [];
            foreach ($attaches as $attach) {
                array_push($attach_lists, [
                    'id' => $attach->id,
                    'title' => $attach->title,
                    'file' => $attach->file,
                    'file_type' => $attach->file_type,
                ]);
            }

            if (count($attach_lists) > 0) return api_response($request, $attach_lists, 200, ['attach_lists' => $attach_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function storeAttachment($business, $log, Request $request)
    {
        try {
            $this->validate($request, [
                'file' => 'required'
            ]);
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $fuel_log = FuelLog::find((int)$log);
            if (!$request->hasFile('file'))
                return redirect()->back();
            $data = $this->storeAttachmentToCDN($request->file('file'));
            $attachment = $fuel_log->attachments()->save(new Attachment($this->withBothModificationFields($data)));
            return api_response($request, $attachment, 200, ['attachment' => $attachment->file]);
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

    public function getComments($business, $log, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $fuel_log = FuelLog::find((int)$log);
            if (!$fuel_log) return api_response($request, null, 404);
            list($offset, $limit) = calculatePagination($request);
            $comments = Comment::where('commentable_type', get_class($fuel_log))->where('commentable_id', $fuel_log->id)->orderBy('id', 'DESC')->skip($offset)->limit($limit)->get();
            $comment_lists = [];
            foreach ($comments as $comment) {
                array_push($comment_lists, [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'user' => [
                        'name' => $comment->commentator->profile->name,
                        'image' => $comment->commentator->profile->pro_pic
                    ],
                    'created_at' => $comment->created_at->toDateTimeString()
                ]);
            }
            if (count($comment_lists) > 0) return api_response($request, $comment_lists, 200, ['comment_lists' => $comment_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function storeComment($business, $log, Request $request)
    {
        try {
            $this->validate($request, [
                'comment' => 'required'
            ]);
            $business = $request->business;
            $member = $request->manager_member;
            $fuel_log = FuelLog::find((int)$log);
            $comment = (new CommentRepository('FuelLog', $fuel_log->id, $member))->store($request->comment);
            return $comment ? api_response($request, $comment, 200) : api_response($request, $comment, 500);
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

}