<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Service;
use App\Repositories\ProfileRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Excel;
use Illuminate\Support\Facades\Storage;

class FacebookController extends Controller
{
    private $fbKit;
    private $profileRepository;
    private $serviceRepository;

    public function __construct()
    {
        $this->fbKit = new FacebookAccountKit();
        $this->profileRepository = new ProfileRepository();
        $this->serviceRepository = new ServiceRepository();
    }

    public function continueWithKit(Request $request)
    {
        //Authenticate the code with account kit
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        //check if user already exists or not
        $profile = $this->profileRepository->ifExist($code_data['mobile'], 'mobile');
        //user doesn't exist
        if ($profile != false) {
            $info = $this->profileRepository->getProfileInfo($request->from, $profile);
            if ($info != false) {
                return response()->json(['code' => 200, 'info' => $info]);
            }
        }
        return response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    public function continueWithFacebook(Request $request)
    {
        $profile = $this->profileRepository->ifExist($request->input('fb_id'), 'fb_id');
        if ($profile != false) {
            $info = $this->profileRepository->getProfileInfo($request->from, $profile);
            if ($info != false) {
                return response()->json(['code' => 200, 'info' => $info]);
            }
        }
        return response()->json(['code' => 404, 'msg' => 'Not found!']);

    }

    public function uploadProducts(Request $request)
    {
        $services = Service::where('publication_status', 1)->get();
        foreach ($services as $service) {
            $this->serviceRepository->getStartPrice($service);
        }
        $filename = 'yes';
        $a = Excel::create($filename, function ($excel) use ($services, $filename) {
            $excel->setTitle($filename);
            $excel->setCreator('Sheba')->setCompany('Sheba');
            $excel->sheet('Order', function ($sheet) use ($services) {
                $sheet->loadView('excel')->with('services', $services);
            });
        })->string('csv');
        $filename = 'products.csv';
        $s3 = Storage::disk('s3');
        $s3->put('uploads/product_feeds' . $filename, $a, 'public');
    }
}
