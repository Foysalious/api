<?php namespace App\Http\Controllers\B2b;

use Carbon\Carbon;
use Sheba\Business\TripRequestApproval\Updater;
use Sheba\Dal\TripRequestApproval\TripRequestApprovalRepositoryInterface;
use Sheba\Dal\TripRequestApproval\Model as TripRequestApproval;
use Illuminate\Validation\ValidationException;
use Sheba\Business\TripRequestApproval\Creator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TripRequestApprovalController extends Controller
{
    public function index(Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $business_member = $request->business_member;
            $trip_request_approvals = TripRequestApproval::where('business_member_id', $business_member->id)
                ->orderBy('id', 'desc')
                ->skip($offset)
                ->limit($limit);
            if ($request->filled('status')) {
                $trip_request_approvals = $trip_request_approvals->where('status', $request->status);
            }

            if ($request->filled('vehicle_type')) {
                $trip_request_approvals = $trip_request_approvals->whereHas('businessTripRequest', function ($query) use ($request) {
                    $query->where('vehicle_type', $request->vehicle_type);
                });
            }

            $start_date = $request->filled('start_date') ? $request->start_date : null;
            $end_date = $request->filled('end_date') ? $request->end_date : null;
            if ($start_date && $end_date) {
                $trip_request_approvals->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }

            $request_approvals = [];
            foreach ($trip_request_approvals->get() as $trip_request_approval) {
                $business_trip_request = $trip_request_approval->businessTripRequest;
                array_push($request_approvals, [
                    'id' => $trip_request_approval->id,
                    'status' => $trip_request_approval->status,
                    'business_trip_request' => [
                        'id' => $business_trip_request->id,
                        'vehicle_type' => $business_trip_request->vehicle_type,
                        'pickup_address' => $business_trip_request->pickup_address,
                        'dropoff_address' => $business_trip_request->dropoff_address,
                        'requested_on' => Carbon::parse($business_trip_request->start_date)->format('d/m/y'),
                        'created_on' => $business_trip_request->created_at->format('d/m/y'),
                    ]
                ]);
            }
            if (count($request_approvals) > 0) return api_response($request, $request_approvals, 200, ['request_approvals' => $request_approvals]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function statusUpdate($member, $approval, Request $request, Updater $updater)
    {
        try {
            $this->validate($request, ['status' => 'required|string']);
            $updater->setMember($request->member)
                ->setBusinessMember($request->business_member)
                ->setTripRequestApproval((int)$approval)
                ->setData($request->all());
            if ($error = $updater->hasError())
                return api_response($request, $error, 400, ['message' => $error]);
            $updater->change();
            return api_response($request, null, 200);
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