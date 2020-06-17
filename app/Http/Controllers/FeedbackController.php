<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;

class FeedbackController extends Controller
{
    use ModificationFields;

    public function create(Request $request, Feedback $feedback) {
        try {
            return api_response($request, null, 500);
            $this->validate($request, [
                'remember_token' => 'required_unless:user,0|string',
                'description' => 'required|string'
            ]);

            $data = $request->all();
            $partner = $request->partner;
            $this->setModifier($partner);
            unset($data["remember_token"], $data["partner"], $data["manager_resource"]);
            $feedback->create($this->withCreateModificationField($data));
            return api_response($request, null, 200);
        }catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
