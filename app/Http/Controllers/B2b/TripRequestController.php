<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Jobs\Business\SendEmailForFleetToB2bTeam;
use App\Models\Business;
use App\Models\BusinessTrip;
use App\Models\BusinessTripRequest;
use App\Repositories\CommentRepository;
use App\Sheba\Business\ACL\AccessControl;
use App\Sheba\Business\BusinessTripSms;
use App\Sheba\Business\TripRequest\Creator;
use FontLib\Table\Type\name;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Sheba\Business\Scheduler\TripScheduler;
use DB;
use Sheba\Location\Geo;
use Sheba\ModificationFields;
use Sheba\Notification\B2b\TripRequests;
use Illuminate\Support\Facades\DB as DBTransaction;
use Throwable;

class TripRequestController extends Controller
{
    use ModificationFields;
    private $b2b_management_emails = ['one' => 'b2b@sheba.xyz'];

    public function getTripRequests(Request $request)
    {
        try {
            $business_member = $request->business_member;
            $type = implode(',', config('business.VEHICLE_TYPES'));
            $this->validate($request, [
                'status' => 'sometimes|string|in:accept,reject,pending',
                'vehicle' => 'sometimes|string|in:' . $type,
            ]);
            $list = [];
            list($offset, $limit) = calculatePagination($request);
            $status = $car_type = null;
            if ($request->has('status')) {
                if ($request->status == "accept") $status = 'accepted';
                elseif ($request->status == "reject") $status = 'rejected';
                else $status = 'pending';
            }
            if ($request->has('vehicle')) $car_type = $request->vehicle;
            $business = $request->business->load(['businessTripRequests' => function ($q) use ($offset, $limit, $status, $car_type) {
                $q->with('member.profile')->orderBy('id', 'desc')->skip($offset)->take($limit);
                if ($status) $q->where('status', $status);
                if ($car_type) $q->where('vehicle_type', $car_type);
            }]);
            if (!$business_member->is_super) {
                $business_trip_requests = $business->businessTripRequests()
                    ->where('member_id', $business_member->member_id)
                    ->get();
            } else {
                $business_trip_requests = $business->businessTripRequests;
            }
            foreach ($business_trip_requests as $business_trip_request) {
                array_push($list, [
                    'id' => $business_trip_request->id,
                    'member' => [
                        'name' => $business_trip_request->member->profile->name,
                        "designation" => $business_trip_request->member->businessMember->role ? $business_trip_request->member->businessMember->role->name : ''
                    ],
                    'vehicle_type' => ucfirst($business_trip_request->vehicle_type),
                    'status' => ucfirst($business_trip_request->status),
                    'created_date' => $business_trip_request->created_at->format('Y-m-d'),
                ]);
            }
            if (count($business_trip_requests) > 0) return api_response($request, $business_trip_requests, 200, ['trip_requests' => $list]);
            else  return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getTrips($member, Request $request)
    {
        try {
            $this->validate($request, [
                'vehicle_id' => 'sometimes|numeric',
                'from' => 'sometimes|date',
                'to' => 'sometimes|date',
            ]);
            $list = [];
            list($offset, $limit) = calculatePagination($request);
            $from = $to = $vehicle_id = null;
            if ($request->has('from')) $from = $request->from;
            elseif ($request->has('to')) $to = $request->to;
            elseif ($request->has('vehicle_id')) $vehicle_id = $request->vehicle_id;
            $business = $business = $request->business->load(['businessTrips' => function ($q) use ($offset, $limit, $from, $to, $vehicle_id) {
                $q->with(['vehicle.basicInformation', 'driver.profile'])->orderBy('id', 'desc')->skip($offset)->take($limit);
                if ($from && $to) {
                    $q->where(function ($q) use ($from, $to) {
                        $q->wherBetween('start_date', $from, $to)->orWhere(function ($q) use ($from, $to) {
                            $q->where('end_date', '>', $to)->whereBetween('start_date', [$from, $to]);
                        });
                    });
                }
                if ($vehicle_id) $q->where('vehicle_id', $vehicle_id);
            }]);
            $business_trips = $business->businessTrips;
            foreach ($business_trips as $business_trip) {
                array_push($list, [
                    'id' => $business_trip->id,
                    'vehicle' => [
                        'name' => $business_trip->vehicle->basicInformation->model_name
                    ],
                    'driver' => [
                        'name' => $business_trip->driver->profile->name,
                        'image' => $business_trip->driver->profile->pro_pic,
                    ],
                    'start_date' => $business_trip->start_date,
                    'end_date' => $business_trip->end_date
                ]);
            }
            if (count($business_trips) > 0) return api_response($request, $business_trips, 200, ['trips' => $list]);
            else  return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $member
     * @param AccessControl $access_control
     * @param $trip_request
     * @param Request $request
     * @return JsonResponse
     */
    public function tripRequestInfo($member, AccessControl $access_control, $trip_request, Request $request)
    {
        try {
            $trip_request = BusinessTripRequest::find((int)$trip_request);
            if (!$trip_request) return api_response($request, null, 404);
            $comments = [];
            foreach ($trip_request->comments as $comment) {
                array_push($comments, [
                    'comment' => $comment->comment,
                    'user' => [
                        'name' => $comment->commentator->profile->name,
                        'image' => $comment->commentator->profile->pro_pic
                    ],
                    'created_at' => $comment->created_at->toDateTimeString()
                ]);
            }
            $trip_request_approvers = [];
            $can_approve = false;
            $can_take_action = $access_control->setBusinessMember($request->business_member)->hasAccess('trip_request.rw');
            if ($request_approvals = $trip_request->tripRequestApprovals->load('businessMember')) {
                foreach ($request_approvals as $trip_request_approval) {
                    $business_member = $trip_request_approval->businessMember;
                    $member = $business_member->member;
                    $profile = $member->profile;
                    if ($business_member->id === $request->business_member->id) $can_approve = true;
                    array_push($trip_request_approvers, [
                        'trip_request_approval_id' => $trip_request_approval->id,
                        'member_id' => $member->id,
                        'name' => $profile->name ? $profile->name : null,
                        'pro_pic' => $profile->pro_pic ? $profile->pro_pic : null,
                        'designation' => $business_member->role ? $business_member->role->name : '',
                        'department' => $business_member->role && $business_member->role->businessDepartment ? $business_member->role->businessDepartment->name : null,
                        'status' => $trip_request_approval->status,
                        'mobile' => $profile->mobile ? $profile->mobile : null,
                    ]);
                }
            }

            $info = [
                'id' => $trip_request->id,
                'reason' => $trip_request->reason,
                'details' => $trip_request->details,
                'member' => [
                    'name' => $trip_request->member->profile->name,
                    "designation" => $trip_request->member->businessMember->role ? $trip_request->member->businessMember->role->name : ''
                ],
                'status' => $trip_request->status,
                'can_approve' => $can_approve ? 1 : 0,
                'can_take_action' => $can_take_action ? 1 : 0,
                'comments' => $comments,
                'vehicle_type' => ucfirst($trip_request->vehicle_type),
                'trip_type' => $trip_request->trip_readable_type,
                'pickup_address' => $trip_request->pickup_address,
                'dropoff_address' => $trip_request->dropoff_address,
                'start_date' => $trip_request->start_date,
                'end_date' => $trip_request->end_date,
                'no_of_seats' => $trip_request->no_of_seats,
                'created_at' => $trip_request->created_at->toDateTimeString(),
                'trip_request_approvers' => $trip_request_approvers
            ];

            return api_response($request, $info, 200, ['info' => $info]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function tripInfo($member, $trip, Request $request)
    {

        try {
            $trip = BusinessTrip::find((int)$trip);
            if (!$trip) return api_response($request, null, 404);
            $comments = [];
            $business_member = $request->business_member;
            foreach ($trip->comments as $comment) {
                array_push($comments, [
                    'comment' => $comment->comment,
                    'user' => [
                        'name' => $comment->commentator->profile->name,
                        'image' => $comment->commentator->profile->pro_pic
                    ],
                    'created_at' => $comment->created_at->toDateTimeString()
                ]);
            }
            $info = [
                'id' => $trip->id,
                'reason' => $trip->reason,
                'details' => $trip->details,
                'member' => [
                    'name' => $trip->member->profile->name,
                    'image' => $trip->member->profile->pro_pic,
                    'designation' => $trip->member->businessMember ? $trip->member->businessMember->role ? $trip->member->businessMember->role->name : 'N/A' : 'N/A'
                ],
                'comments' => $comments,
                'driver' => [
                    'name' => $trip->driver->profile->name,
                    'mobile' => $trip->driver->profile->mobile,
                    'image' => $trip->driver->profile->pro_pic
                ],
                'vehicle' => [
                    'name' => $trip->vehicle->basicInformation->model_name,
                    'type' => $trip->vehicle->basicInformation->readable_type,
                ],
                'vehicle_type' => ucfirst($trip->vehicle_type),
                'trip_type' => $trip->trip_readable_type,
                'pickup_address' => $trip->pickup_address,
                'dropoff_address' => $trip->dropoff_address,
                'start_date' => $trip->start_date,
                'end_date' => $trip->end_date,
                'no_of_seats' => $trip->no_of_seats,
                'created_at' => $trip->created_at->toDateTimeString(),
            ];
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param BusinessTripSms $businessTripSms
     * @param Creator $trip_request_creator
     * @return JsonResponse
     */
    public function createTrip(Request $request, BusinessTripSms $businessTripSms, Creator $trip_request_creator)
    {
        try {
            $business_member = $request->business_member;
            $will_auto_assign = (int)$business_member->is_super || $business_member->actions()->where('tag', config('business.actions.trip_request.rw'))->first();
            $this->validate($request, ['status' => 'required|string|in:accept,reject']);
            if ($request->has('trip_request_id')) {
                $business_trip_request = BusinessTripRequest::find((int)$request->trip_request_id);
                if ($business_trip_request->status != 'pending' && !$will_auto_assign) return api_response($request, null, 403);
            } else {
                $trip_request_creator->setBusinessMember($business_member)->setDriverId($request->driver_id)->setVehicleId($request->vehicle_id)
                    ->setPickupAddress($request->pickup_address)->setDropoffAddress($request->dropoff_address)->setStartDate($request->start_date)
                    ->setEndDate($request->end_date)->setTripType($request->trip_type)->setVehicleType($request->vehicle_type)->setReason($request->reason)
                    ->setDetails($request->details)->setNoOfSeats($request->no_of_seats);

                if ($request->has('pickup_lat')) $trip_request_creator->setPickupGeo((new Geo())->setLat($request->pickup_lat)->setLng($request->pickup_lng));
                if ($request->has('dropoff_lat')) $trip_request_creator->setPickupGeo((new Geo())->setLat($request->dropoff_lat)->setLng($request->dropoff_lng));

                $business_trip_request = $trip_request_creator->create();
            }
            DBTransaction::beginTransaction();
            $super_admins = Business::find((int)$business_trip_request->business_id)->superAdmins;
            $trip_requests = new TripRequests();
            $trip_requests->setMember($request->member)
                ->setBusinessMember($business_member)
                ->setBusinessTripRequest($business_trip_request)
                ->setSuperAdmins($super_admins);

            if ($request->has('status') && $request->status == "accept") {
                $business_trip_request->vehicle_id = $request->vehicle_id;
                $business_trip_request->driver_id = $request->driver_id;
                $business_trip_request->status = 'accepted';
                $business_trip_request->update();

                $trip_requests->setNotificationTitle($trip_requests->getRequesterIdentity(false, true) . ' has accepted trip request.')
                    ->setEmailSubject('Trip Request Accepted')
                    ->setEmailTemplate('emails.trip_request_accepted_notifications')
                    ->setEmailTitle($trip_requests->getRequesterIdentity(false, true) . ' has accepted trip request.')
                    ->setVehicle($request->vehicle_id)
                    ->setDriver($request->driver_id)
                    ->notifications(true, 'TripAccepted', false, true);

                $business_trip = $this->storeTrip($business_trip_request);
                $businessTripSms->setTrip($business_trip)->sendTripRequestAccept();
                DBTransaction::commit();
                return api_response($request, $business_trip, 200, ['id' => $business_trip->id]);
            } else {
                $business_trip_request->status = 'rejected';
                $business_trip_request->update();

                $trip_requests->setNotificationTitle($trip_requests->getRequesterIdentity(false, true) . ' has rejected trip request.')
                    ->setEmailSubject('Trip Request Rejected')
                    ->setEmailTemplate('emails.trip_request_accepted_notifications')
                    ->setEmailTitle($trip_requests->getRequesterIdentity(false, true) . ' has rejected trip request.')
                    ->notifications(true, 'TripAccepted', false, true);

                DBTransaction::commit();
                return api_response($request, null, 200, ['message' => 'Trip Request rejected successfully']);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            DBTransaction::rollback();
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param TripScheduler $vehicleScheduler
     * @param BusinessTripSms $businessTripSms
     * @param Creator $trip_request_creator
     * @return JsonResponse
     */
    public function createTripRequests(Request $request, TripScheduler $vehicleScheduler, BusinessTripSms $businessTripSms, Creator $trip_request_creator)
    {
        $business_member = $request->business_member;
        $business_trip_request = null;
        $this->setModifier($request->member);
        DB::transaction(function () use ($request, $business_member, $vehicleScheduler, $businessTripSms, $trip_request_creator, &$business_trip_request) {
            $trip_request_creator->setBusinessMember($business_member)->setDriverId($request->driver_id)->setVehicleId($request->vehicle_id)
                ->setPickupAddress($request->pickup_address)->setDropoffAddress($request->dropoff_address)->setStartDate($request->start_date)
                ->setEndDate($request->end_date)->setTripType($request->trip_type)->setVehicleType($request->vehicle_type)->setReason($request->reason)
                ->setDetails($request->details)->setNoOfSeats($request->no_of_seats);

            if ($request->has('pickup_lat')) $trip_request_creator->setPickupGeo((new Geo())->setLat($request->pickup_lat)->setLng($request->pickup_lng));
            if ($request->has('dropoff_lat')) $trip_request_creator->setPickupGeo((new Geo())->setLat($request->dropoff_lat)->setLng($request->dropoff_lng));

            $business_trip_request = $trip_request_creator->create();

            $super_admins = Business::find((int)$business_trip_request->business_id)->superAdmins;
            $trip_requests = new TripRequests();
            $trip_requests->setMember($request->member)
                ->setBusinessMember($business_member)
                ->setBusinessTripRequest($business_trip_request)
                ->setSuperAdmins($super_admins)
                ->setNotificationTitle($trip_requests->getRequesterIdentity() . ' has created a new trip request.')
                ->setEmailSubject('New Trip Request')
                ->setEmailTemplate('emails.trip_request_create_notifications')
                ->setEmailTitle($trip_requests->getRequesterIdentity() . ' has created a new trip request.')
                ->notifications(true, 'TripCreate', false, false);

            $will_auto_assign = (int)$business_member->is_super ||
                $business_member->actions()->where('tag', config('business.actions.trip_request.auto_assign'))->first();

            if ($will_auto_assign) {
                $vehicleScheduler->setStartDate($request->start_date)
                    ->setEndDate($request->end_date)
                    ->setBusinessDepartment($business_member->role->businessDepartment)
                    ->setBusiness($request->business);

                $vehicles = $vehicleScheduler->getFreeVehicles();
                $drivers = $vehicleScheduler->getFreeDrivers();
                if ($vehicles->count() > 0) $vehicle = $vehicles->random();
                if ($drivers->count() > 0) $driver = $drivers->random();
                if (!(isset($vehicle) && isset($driver))) {
                    return api_response($request, null, 500, ["message" => "There is no free vehicle or driver"]);
                }

                $business_trip_request->vehicle_id = $vehicle;
                $business_trip_request->driver_id = $driver;
                $business_trip_request->status = 'accepted';
                $business_trip_request->update();

                $business_trip = $this->storeTrip($business_trip_request);
                $businessTripSms->setTrip($business_trip)->sendTripRequestAccept();
            }
        });

        return api_response($request, $business_trip_request, 200, ['id' => $business_trip_request->id]);
    }

    public function commentOnTripRequest($member, $trip_request, Request $request)
    {
        try {
            $this->validate($request, [
                'comment' => 'required'
            ]);

            DBTransaction::beginTransaction();
            $business_member = $request->business_member;
            $business_trip_request = BusinessTripRequest::findOrFail((int)$trip_request);
            $super_admins = Business::find((int)$business_trip_request->business_id)->superAdmins;
            $trip_requests = new TripRequests();
            $trip_requests->setMember($request->member)
                ->setBusinessMember($business_member)
                ->setSuperAdmins($super_admins)
                ->setBusinessTripRequest($business_trip_request)
                ->setNotificationTitle($trip_requests->getRequesterIdentity(true) . ' commented on trip request.')
                ->notifications(false, null, true, false);

            $comment = (new CommentRepository('BusinessTripRequest', $trip_request, $request->member))->store($request->comment);
            DBTransaction::commit();
            return $comment ? api_response($request, $comment, 200) : api_response($request, $comment, 500);
        } catch (Throwable $e) {
            DBTransaction::rollback();
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function commentOnTrip($member, $trip, Request $request)
    {
        try {
            $comment = (new CommentRepository('BusinessTrip', $trip, $request->member))->store($request->comment);
            return $comment ? api_response($request, $comment, 200) : api_response($request, $comment, 500);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param BusinessTripRequest $business_trip_request
     * @return BusinessTrip
     */
    private function storeTrip(BusinessTripRequest $business_trip_request)
    {
        $business_trip = new BusinessTrip();
        $business_trip->business_trip_request_id = $business_trip_request->id;
        $business_trip->member_id = $business_trip_request->member_id;
        $business_trip->driver_id = $business_trip_request->driver_id;
        $business_trip->business_id = $business_trip_request->business_id;
        $business_trip->vehicle_id = $business_trip_request->vehicle_id;
        $business_trip->pickup_geo = $business_trip_request->pickup_geo;
        $business_trip->dropoff_geo = $business_trip_request->dropoff_geo;
        $business_trip->pickup_address = $business_trip_request->pickup_address;
        $business_trip->dropoff_address = $business_trip_request->dropoff_address;
        $business_trip->start_date = $business_trip_request->start_date;
        $business_trip->end_date = $business_trip_request->end_date;
        $business_trip->trip_type = $business_trip_request->trip_type;
        $business_trip->reason = $business_trip_request->reason;
        $business_trip->details = $business_trip_request->details;
        $business_trip->no_of_seats = $business_trip_request->no_of_seats;
        $this->withCreateModificationField($business_trip);
        $business_trip->save();

        return $business_trip;
    }

    /**
     * @param $member
     * @param Request $request
     * @return JsonResponse
     */
    public function fleetMail($member, Request $request)
    {
        $business = $request->business;
        foreach ($this->b2b_management_emails as $management_email) {
            $this->sendMailToB2bTeam($business, $management_email);
        }
        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param $to_email
     */
    private function sendMailToB2bTeam($business, $to_email)
    {
        $this->dispatch(new SendEmailForFleetToB2bTeam($business, $to_email));
    }
}
