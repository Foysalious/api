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
                'data' => [
                     '১. ১৫ হাজার টাকার অধিক মূল্যের পণ্য কিস্তিতে বিক্রি করতে পারবেন। যা আপনার বিক্রি বাড়াবে।',
                     '২. কিস্তির বকেয়া টাকা আপনাকে বহন করতে হবে না, ব্যাংক বহন করবে।',
                     '৩. POS মেশিন ছাড়াই ক্রেডিট কার্ড এর মাধ্যমে EMI তে বিক্রি করতে পারবেন ।'
                ]
            ),
            array(
                'tag'          => 'how_to_emi',
                'header_label' => 'কিস্তি (EMI) সুবিধা কিভাবে দিবেন-',
                'how_to_emi'   => [
                    '১. POS. থেকে পণ্য সিলেক্ট করুন অথবা৷ EMI থেকে বিক্রির সমমূল্যের টাকা নির্ধারন করুন।',
                    '২. EMI এর লিংক কাস্টমার এর সাথে শেয়ার করুন।',
                    '৩. কাস্টমার প্রেমেন্ট নিশ্চিত করলে আপনার সেবা ক্রেডিট এ টাকা চেক করে পণ্য বুঝিয়ে দিন।'
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
                $data = $repository->setPartner($request->getPartner())->getRecent();
            } else {
                $data = $repository->setPartner($request->getPartner())->setOffset($request->getOffset())->setLimit($request->getLimit());
                if ($request->hasQuery()) {
                    $data = $data->setQuery($request->getQuery());
                }
                $data = $data->get();
            }
            return api_response($request->original(), null, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request->original(), null, 500);
        }
    }

    public function details(Request $request, $partner_id, $id, EMIRepository $repository) {
        $request=RequestFilter::get();
        $data = $repository->setPartner($request->getPartner())->details((int)$id);
        return api_response($request->original(), $data, 200, ['data' => $data]);
    }
}
