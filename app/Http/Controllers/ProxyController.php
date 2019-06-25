<?php namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\TopUp\Vendor\Internal\Pretups\DirectCaller;

class ProxyController extends Controller
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function pretupsTopUp(Request $request, DirectCaller $caller)
    {
        try {
            $whitelists = ['180.234.223.46', '104.215.190.77', '13.232.181.83', '103.4.146.66'];
            if (!in_array($request->ip(), $whitelists)) {
                $sentry = app('sentry');
                $sentry->user_context(['ip' => $request->ip()]);
                $sentry->captureException(new \Exception('Unauthorized ip'));
                return api_response($request, null, 401);
            }
            $this->validate($request, [
                'url' => 'required|string',
                'input' => 'required|string',
            ]);
            $data = $caller->setUrl($request->url)->setInput($request->input)->call();

            return api_response($request, 1, 200, ['endpoint_response' => $data]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400);
        } catch (\Throwable $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            return api_response($request, null, 500);
        }
    }
}