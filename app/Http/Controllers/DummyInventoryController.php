<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class DummyInventoryController extends Controller
{

    public function brandStore(Request $request, $partner)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
            ]);

            return api_response($request, null, 200, ['message' => 'ব্র্যান্ডটি যোগ করা হয়েছে']);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function brandList(Request $request, $partner)
    {
        try {
            $brands = ['brand1','brand2','brand3'];
            return api_response($request, null, 200, ['brands' => $brands]);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function brandUpdate(Request $request, $partner, $brand)
    {
        try {
                $this->validate($request, [
                    'name' => 'required',
                ]);
            return api_response($request, null, 200, ['message' => 'ব্র্যান্ডটি পরিবর্তন করা হয়েছে']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }


    public function unitStore(Request $request, $partner)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
            ]);

            return api_response($request, null, 200, ['message' => 'পণ্যের একক যোগ করা হয়েছে']);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function unitList(Request $request, $partner)
    {
        try {
            $units  = ['ft','sft','km', 'carton'];
            return api_response($request, null, 200, ['units' => $units]);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function unitUpdate(Request $request, $partner, $unit)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
            ]);
            return api_response($request, null, 200, ['message' => 'এককটি পরিবর্তন করা হয়েছে']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

}