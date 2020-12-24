<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\Newsletter\Model as Newsletter;
use Illuminate\Http\Request;
use Sheba\Newsletter\Creator;

class NewsletterController extends Controller
{
    public function create(Request $request, Creator $creator)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
                'portal_name' => 'required|string|in:' . implode(',', config('sheba.portals'))
            ]);
            $creator->setEmail($request->email)->setPortalName($request->portal_name)->setIp($request->ip());
            $creator->store();
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}