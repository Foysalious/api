<?php namespace App\Http\Controllers\EKYC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\EKYC\EkycClient;
use Sheba\EKYC\Exceptions\EkycServerError;


class FaceVerificationController extends Controller
{
    private $client;
    private $api;

    public function __construct(EkycClient $client)
    {
        $this->client = $client;
        $this->api = 'face-verification';
    }

    public function faceVerification(Request $request)
    {
        try {
            $data = $this->toData($request);
            $userId = isset($request->user_id) ? $request->user_id : 1;
            return $this->client->setUserId($userId)->post($this->api, $data);
        } catch (EkycServerError $e) {
            throw new EkycServerError($e->getMessage(), $e->getCode());
        }
    }

    private function toData($request)
    {
        $data['person_photo'] = $request->person_photo;
        return $data;
    }
}
