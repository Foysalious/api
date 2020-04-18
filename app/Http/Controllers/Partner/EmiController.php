<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\EMI\Repository as EMIRepository;
use Sheba\EMI\RequestFilter;

class EmiController extends Controller {
    public function __construct() {
    }

    public function index(Request $request) {
        $minimum_amount = config('emi.minimum_emi_amount');
        $emi_home       = array(
            array(
                'tag'          => 'emi_benefits',
                'header_label' => 'কিস্তি (EMI) এর সুবিধা কি কি-',
                'emi_benefits' => [
                     'সহজেই ব্যবসা বার্তা পৌঁছে দিন কাস্টমার এর কাছে ',
                     'আপনার সুবিধামত সময়েও বাজেটে সল্পমূল্যে কার্যকরী ',
                     'শুধু সফলভাবে পাঠানো এসএমএস বা ইমেইলের জন্যই মূল্য দিন ',
                     'থেকে অর্ডার পাবার রিপোর্ট পাচ্ছেন খুব দ্রুত ',
                ]
            ),
            array(
                'tag'          => 'how_to_emi',
                'header_label' => 'কিস্তি (EMI) সুবিধা কিভাবে দেবেন-',
                'how_to_emi'   => [
                    '১। সহজেই ব্যবসা বার্তা পৌঁছে দিন কাস্টমারের কাছে ',
                    '২। আপনার সুবিধামত সময়ে ও বাজেটে সল্পমূল্যে কার্যকরী মার্কেটিং',
                    '৩। শুধু সফলভাবে পাঠানো এসএমএস বা ইমেইলের জন্যই মূল্য দিন '
                ]
            )
        );
        $data           = [$minimum_amount, $emi_home];
        return api_response($request, $data, 200, ['minimum_amount' => 15000, 'emi_home' => $emi_home]);
    }

    public function emiList(EmiRepository $repository) {

        $request = RequestFilter::get();
        try {
            if ($request->isRecent()) {
                $data = $repository->getRecent();
            } else {
                $data = $repository->get();
            }
            return api_response($request->original(), null, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request->original(), null, 500);
        }
    }

    public function details(Request $request, $partner_id, $id, EMIRepository $repository) {
        $data = $repository->details((int)$id);
        return api_response($request, $data, 200, ['data' => $data]);
    }
}
