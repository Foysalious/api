<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class EmiController extends Controller
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $minimum_amount=config('emi.minimum_emi_amount');
        $emi_home = array(
            array('tag' => 'emi_benefits', 'header_label' => 'কিস্তি (EMI) এর সুবিধা কি কি-',
                'emi_benefits' => [
                    '1'=>'সহজেই ব্যবসা বার্তা পৌঁছে দিন কাস্টমার এর কাছে ',
                    '2'=>'আপনার সুবিধামত সময়েও বাজেটে সল্পমূল্যে কার্যকরী ',
                    '3'=>'শুধু সফলভাবে পাঠানো এসএমএস বা ইমেইলের জন্যই মূল্য দিন ',
                    '4'=>'থেকে অর্ডার পাবার রিপোর্ট পাচ্ছেন খুব দ্রুত ',
                ]
            ),
            array('tag' => 'how_to_emi', 'header_label' => 'কিস্তি (EMI) সুবিধা কিভাবে দেবেন-', 'group_name' => 'service_refer',
                'how_to_emi' => [
                    '1'=>'১। সহজেই ব্যবসা বার্তা পৌঁছে দিন কাস্টমারের কাছে ',
                    '2'=>'২। আপনার সুবিধামত সময়ে ও বাজেটে সল্পমূল্যে কার্যকরী মার্কেটিং',
                    '3'=>'৩। শুধু সফলভাবে পাঠানো এসএমএস বা ইমেইলের জন্যই মূল্য দিন '
                ]
            )
        );
        $data=[$minimum_amount, $emi_home];
        return api_response($request, $data, 200, ['minimum_amount'=>15000, 'emi_home' => $emi_home]);
    }
}