<?php namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProxyController extends Controller
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function pretupsTopUp(Request $request)
    {
        try {
            $whitelists = ['180.234.223.46', '104.215.190.77', '13.232.181.83', '172.18.0.6'];
            if(!in_array($request->ip(), $whitelists)) {
                return ['status' => 401];
            }

            $this->validate($request, [
                'url' => 'required|string',
                'input' => 'required|string',
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request->url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml', 'Connection: close']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "xmlRequest=$request->input");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
            $data = curl_exec($ch);
            $err = curl_error($ch);
            if($err) throw new \Exception($err);
            curl_close($ch);
            $data = json_decode(json_encode(simplexml_load_string($data)), 1);

            return api_response($request, 1, 200, ['endpoint_response' => $data]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}