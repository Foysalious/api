<?php

namespace App\Http\Controllers;

use App\Repositories\CustomerRepository;
use App\Repositories\ProfileRepository;
use App\Sheba\Bondhu\BondhuAutoOrder;
use App\Sheba\Eksheba\EkshebaAuthenticate;
use App\Sheba\Eksheba\EkshebaOrder;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EkshebaController extends Controller
{
    private $customer;
    private $fbKit;
    private $fileRepository;
    private $profileRepository;

    public function __construct()
    {
        $this->customer = new CustomerRepository();
        $this->fbKit = new FacebookAccountKit();
        $this->profileRepository = new ProfileRepository();
    }

    public function authenticate(Request $request, EkshebaAuthenticate $authenticate){
        try{
            $this->validate($request,[
                'eksheba_token' => 'required'
            ]);

            $response = $this->_getEkshebaUserInfo($request->eksheba_token);

            if($response->status) {
                if(isset($response->data)) {
                    $user = $response->data;
                    $customer = $authenticate->setName($user->name_en)->setMobile($user->mobile)->setaffiliate()->getaffiliate();
                    $customer->name = $user->name_en;
                    $customer->eksheba_token = $response->data->token;
                    $customer->auth_token = $customer->remember_token;
                    return api_response($request, null, 200,  ['user'=> $customer]);
                }
            }
            return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, ['message'=> $e->getMessage()]);
        }
    }

    public function saveEkshebaData(Request $request, EkshebaOrder $order)
    {
        $response = $order->generateOrder($request->token,$request->name,$request->amount);
        return api_response($request, null, 200,  [$response]);
    }
    
    private function _getEkshebaUserInfo($eksheba_token)
    {
        try {
            $url = env('EKSHEBA_API_URL') . '/login/' . $eksheba_token;
            return json_decode((new Client())->get($url)->getBody()->getContents());
        } catch (\Throwable $e) {
            return "User not found";
        }
    }
}
