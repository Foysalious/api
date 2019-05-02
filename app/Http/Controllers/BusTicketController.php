<?php namespace App\Http\Controllers;

use App\Transformers\BusRouteTransformer;
use App\Transformers\CustomSerializer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Transport\Bus\Generators\Routes;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;
use Throwable;

class BusTicketController extends Controller
{
    /** @var BusRouteLocationRepository $busRouteRepo */
    private $busRouteRepo;

    public function __construct(BusRouteLocationRepository $bus_route_repo)
    {
        $this->busRouteRepo = $bus_route_repo;
    }

    /**
     * @param Request $request
     * @param Routes $routes
     * @return JsonResponse
     */
    public function getAvailablePickupPlaces(Request $request, Routes $routes)
    {
        try {
            $pickup_routes = $this->busRouteRepo->get();
            if ($pickup_routes->isEmpty()) {
                $pickup_routes = $routes->generate() ? $this->busRouteRepo->get() : [];
            }
            $routes = [];
            $pickup_routes->each(function ($route) use (&$routes) {
                $manager = new Manager();
                $manager->setSerializer(new CustomSerializer());
                $resource = new Item($route, new BusRouteTransformer());
                $pickup_routes = $manager->createData($resource)->toArray();
                $routes[] = $pickup_routes['data'];
            });

            return api_response($request, $pickup_routes, 200, ['routes' => $routes]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAvailableDestinationPlaces(Request $request)
    {
        try {
            $this->validate($request, ['pickup_place_id' => 'required']);
            $destination_routes = [['id' => 12312321321, 'name' => 'Dhaka'], ['id' => 1912321321, 'name' => 'Chittagong'], ['id' => 12341994124, 'name' => 'Rajshahi'], ['id' => 1234199414224, 'name' => 'Bagerhat'],];

            return api_response($request, $destination_routes, 200, ['routes' => $destination_routes]);
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

    public function getAvailableCoaches(Request $request)
    {
        try {
            $this->validate($request, ['pickup_place_id' => 'required', 'destination_place_id' => 'required', 'date' => 'required',]);

            $available_coaches = [['id' => 121321231, 'company_name' => 'Shyamoli', 'type' => 'AC Coach', 'start_time' => '10.30 pm', 'start_point' => 'Kalabagan', 'end_time' => '8:45 am', 'end_point' => 'Kolatoli', 'price' => '900', 'seats_left' => 15, 'code' => '125-CTG'], ['id' => 121321123231, 'company_name' => 'Shyamoli', 'type' => 'AC Coach', 'start_time' => '10.30 pm', 'start_point' => 'Kalabagan', 'end_time' => '8:45 am', 'end_point' => 'Kolatoli', 'price' => '900', 'seats_left' => 15, 'code' => '125-CTG'], ['id' => 121312321231, 'company_name' => 'Shyamoli', 'type' => 'AC Coach', 'start_time' => '10.30 pm', 'start_point' => 'Kalabagan', 'end_time' => '8:45 am', 'end_point' => 'Kolatoli', 'price' => '900', 'seats_left' => 15, 'code' => '125-CTG'], ['id' => 121321231231, 'company_name' => 'Shyamoli', 'type' => 'AC Coach', 'start_time' => '10.30 pm', 'start_point' => 'Kalabagan', 'end_time' => '8:45 am', 'end_point' => 'Kolatoli', 'price' => '900', 'seats_left' => 15, 'code' => '125-CTG'],];

            $filters = ['types' => [['type' => 'ac', 'name' => 'AC'], ['type' => 'morning_shift', 'name' => 'Morning Shift'], ['type' => 'evening_shift', 'name' => 'Evening Shift'], ['type' => 'morning_shift', 'name' => 'Morning Shift'],]];

            $data = ['coaches' => $available_coaches, 'filters' => $filters];

            return api_response($request, $data, 200, ['data' => $data]);

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

    public function getSeatStatus(Request $request)
    {
        try {
            $this->validate($request, ['coach_id' => 'required',]);

            $boarding_points = array(0 => array('reporting_branch_id' => 183, 'counter_name' => 'Kalabagan Counter', 'reporting_time' => '06:00 AM', 'schedule_time' => '06:00',), 1 => array('reporting_branch_id' => 184, 'counter_name' => 'Kallyanpur counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 2 => array('reporting_branch_id' => 185, 'counter_name' => 'Technical Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 3 => array('reporting_branch_id' => 186, 'counter_name' => 'Gabtoli Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 4 => array('reporting_branch_id' => 187, 'counter_name' => 'Savar Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 5 => array('reporting_branch_id' => 188, 'counter_name' => 'Nabinagar Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 6 => array('reporting_branch_id' => 189, 'counter_name' => 'Baipail Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 7 => array('reporting_branch_id' => 190, 'counter_name' => 'Chandora Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',),);
            $dropping_points = array(0 => array('reporting_branch_id' => 192, 'counter_name' => 'Kachikata Counter', 'reporting_time' => '10:05 AM', 'schedule_time' => '10:05',), 1 => array('reporting_branch_id' => 309, 'counter_name' => 'Noyabazar Counter', 'reporting_time' => '10:10 AM', 'schedule_time' => '10:10',), 2 => array('reporting_branch_id' => 320, 'counter_name' => 'Boraigram Counter', 'reporting_time' => '10:15 AM', 'schedule_time' => '10:15',), 3 => array('reporting_branch_id' => 191, 'counter_name' => 'Bonpara Counter', 'reporting_time' => '10:30 AM', 'schedule_time' => '10:30',), 4 => array('reporting_branch_id' => 193, 'counter_name' => 'Natore Counter', 'reporting_time' => '10:40 AM', 'schedule_time' => '10:40',), 5 => array('reporting_branch_id' => 194, 'counter_name' => 'Puthia Counter', 'reporting_time' => '10:55 AM', 'schedule_time' => '10:55',), 6 => array('reporting_branch_id' => 195, 'counter_name' => 'Baneshore Counter', 'reporting_time' => '11:10 AM', 'schedule_time' => '11:10',), 7 => array('reporting_branch_id' => 310, 'counter_name' => 'Katakhali-2', 'reporting_time' => '11:20 AM', 'schedule_time' => '11:20',), 8 => array('reporting_branch_id' => 321, 'counter_name' => 'Binodpur Counter', 'reporting_time' => '11:30 AM', 'schedule_time' => '11:30',), 9 => array('reporting_branch_id' => 206, 'counter_name' => 'Kajla Counter', 'reporting_time' => '11:35 AM', 'schedule_time' => '11:35',), 10 => array('reporting_branch_id' => 197, 'counter_name' => 'Rajshahi Counter', 'reporting_time' => '11:40 AM', 'schedule_time' => '11:40',), 11 => array('reporting_branch_id' => 312, 'counter_name' => 'City by pass Counter', 'reporting_time' => '11:45 AM', 'schedule_time' => '11:45',), 12 => array('reporting_branch_id' => 311, 'counter_name' => 'Char Kothar Mor Counter', 'reporting_time' => '11:50 AM', 'schedule_time' => '11:50',), 13 => array('reporting_branch_id' => 315, 'counter_name' => 'Kashidanga Counter', 'reporting_time' => '11:55 AM', 'schedule_time' => '11:55',),);
            $seatStatus = ['seats' => [["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Available", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 1, "y_axis" => 1], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Sold", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 1, "y_axis" => 2], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Blocked", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 1, "y_axis" => 3], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Available", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 1, "y_axis" => 4], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Available", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 2, "y_axis" => 1], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Available", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 2, "y_axis" => 2], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Available", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 2, "y_axis" => 3],], "maximum_selectable" => 5, "total_seat_col" => 5, "total_seat_row" => 10, 'boarding_points' => $boarding_points, 'dropping_points' => $dropping_points];


            return api_response($request, $seatStatus, 200, ['seat_status' => $seatStatus]);
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

    public function getAvailablePoints(Request $request)
    {
        try {
            $this->validate($request, ['coach_id' => 'required',]);

            $boarding_points = array(0 => array('reporting_branch_id' => 183, 'counter_name' => 'Kalabagan Counter', 'reporting_time' => '06:00 AM', 'schedule_time' => '06:00',), 1 => array('reporting_branch_id' => 184, 'counter_name' => 'Kallyanpur counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 2 => array('reporting_branch_id' => 185, 'counter_name' => 'Technical Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 3 => array('reporting_branch_id' => 186, 'counter_name' => 'Gabtoli Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 4 => array('reporting_branch_id' => 187, 'counter_name' => 'Savar Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 5 => array('reporting_branch_id' => 188, 'counter_name' => 'Nabinagar Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 6 => array('reporting_branch_id' => 189, 'counter_name' => 'Baipail Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 7 => array('reporting_branch_id' => 190, 'counter_name' => 'Chandora Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',),);
            $dropping_points = array(0 => array('reporting_branch_id' => 192, 'counter_name' => 'Kachikata Counter', 'reporting_time' => '10:05 AM', 'schedule_time' => '10:05',), 1 => array('reporting_branch_id' => 309, 'counter_name' => 'Noyabazar Counter', 'reporting_time' => '10:10 AM', 'schedule_time' => '10:10',), 2 => array('reporting_branch_id' => 320, 'counter_name' => 'Boraigram Counter', 'reporting_time' => '10:15 AM', 'schedule_time' => '10:15',), 3 => array('reporting_branch_id' => 191, 'counter_name' => 'Bonpara Counter', 'reporting_time' => '10:30 AM', 'schedule_time' => '10:30',), 4 => array('reporting_branch_id' => 193, 'counter_name' => 'Natore Counter', 'reporting_time' => '10:40 AM', 'schedule_time' => '10:40',), 5 => array('reporting_branch_id' => 194, 'counter_name' => 'Puthia Counter', 'reporting_time' => '10:55 AM', 'schedule_time' => '10:55',), 6 => array('reporting_branch_id' => 195, 'counter_name' => 'Baneshore Counter', 'reporting_time' => '11:10 AM', 'schedule_time' => '11:10',), 7 => array('reporting_branch_id' => 310, 'counter_name' => 'Katakhali-2', 'reporting_time' => '11:20 AM', 'schedule_time' => '11:20',), 8 => array('reporting_branch_id' => 321, 'counter_name' => 'Binodpur Counter', 'reporting_time' => '11:30 AM', 'schedule_time' => '11:30',), 9 => array('reporting_branch_id' => 206, 'counter_name' => 'Kajla Counter', 'reporting_time' => '11:35 AM', 'schedule_time' => '11:35',), 10 => array('reporting_branch_id' => 197, 'counter_name' => 'Rajshahi Counter', 'reporting_time' => '11:40 AM', 'schedule_time' => '11:40',), 11 => array('reporting_branch_id' => 312, 'counter_name' => 'City by pass Counter', 'reporting_time' => '11:45 AM', 'schedule_time' => '11:45',), 12 => array('reporting_branch_id' => 311, 'counter_name' => 'Char Kothar Mor Counter', 'reporting_time' => '11:50 AM', 'schedule_time' => '11:50',), 13 => array('reporting_branch_id' => 315, 'counter_name' => 'Kashidanga Counter', 'reporting_time' => '11:55 AM', 'schedule_time' => '11:55',),);
            $data = ['boarding_points' => $boarding_points, 'dropping_points' => $dropping_points];
            return api_response($request, $data, 200, ['data' => $data]);
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

}
