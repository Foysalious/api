<?php namespace App\Http\Controllers\Auth\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Logs\ErrorLog;

class LoginController extends Controller
{
    public function login(Request $request, ErrorLog $error_log)
    {
        try {
            return api_response($request, true, 200, ['data' => ['token' => str_random(30)]]);
        } catch (ValidationException $e) {
            return api_response($request, null, 401, ['message' => 'Invalid mobile number']);
        } catch (\Throwable $e) {
            $error_log->setException($e)->send();
            return api_response($request, null, 500);
        }
    }
}