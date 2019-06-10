<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Business\FuelLog\Creator;
use Sheba\ModificationFields;

class FuelLogController extends Controller
{
    use ModificationFields;

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
            $this->setModifier($request->manager_member);
            $fuel_log = $creator->setVehicleId($request->vehicle_id)->setDate($request->date)
                ->setPrice($request->price)->setVolume($request->volume)->setUnit($request->unit)->setType($request->type)
                ->setStationName($request->station_name)->setStationAddress($request->station_address)
                ->setReference($request->reference)
                ->save();
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