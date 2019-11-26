<?php namespace App\Http\Controllers\RentACar;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RentACarController extends Controller
{
    public function getPrices(Request $request)
    {
        try {
            $this->validate($request, ['services' => 'required|string']);
            return api_response($request, null, 200, ['price' => [
                'discounted_price' => 100,
                'original_price' => 140,
                'discount' => 5,
                'quantity' => 10
            ]]);
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