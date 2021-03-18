<?php namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Referral\Exceptions\AlreadyExistProfile;
use Sheba\Referral\Exceptions\AlreadyReferred;
use Sheba\Referral\Exceptions\InvalidFilter;
use Sheba\Referral\Exceptions\ReferenceNotFound;
use Sheba\Referral\Referrals;
use Sheba\UrlShortener\ShortenUrl;
use Throwable;

class PartnerReferralController extends Controller
{

    public function index(Request $request, Referrals $referrals)
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_execution_time', 420);

        try {
            $partner       = $request->partner;
            $reference     = $referrals::getReference($partner);
            $refers        = $reference->getReferrals($request);
            $income        = $reference->totalIncome();
            $total_sms     = $reference->totalRefer();
            $total_success = $reference->totalSuccessfulRefer();
            $steps=collect(config('partner.referral_steps'))->where('visible', true);
            return api_response($request, $reference->refers, 200, [
                'data' => [
                    'refers'          => $refers,
                    'total_income'    => $income,
                    'total_sms'       => $total_sms,
                    'total_success'   => $total_success,
                    'total_step'      => count($steps),
                    'stepwise_income' =>
                        $steps->map(function ($item) {
                            return $item['amount'];
                        })
                ]
            ]);
        } catch (InvalidFilter $e) {
            return api_response($request, null, 500, ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function setReference(Request $request)
    {

    }

    /**
     * @param Request    $request
     * @param ShortenUrl $shortenUrl
     * @return JsonResponse
     */
    public function referLinkGenerate(Request $request, ShortenUrl $shortenUrl)
    {
        try {
            /** @var Partner $partner */
            $partner    = $request->partner;
            $refer_code = $partner->refer_code;
            if (empty($refer_code)) $refer_code = $partner->referCode();
            $partner->update(['refer_code' => $refer_code]);
            return api_response($request, $refer_code, 200, ['link' => $refer_code]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function home(Request $request, Referrals $referrals)
    {
        try {
            $partner = $request->partner;
            $ref     = $referrals::getReference($partner)->home();
            return api_response($request, $ref, 200, ['data' => $ref]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request, $partner, $referral, Referrals $referrals)
    {
        try {
            $ref = $referrals::getReference($request->partner)->details($referral);
            return api_response($request, $ref, 200, ['data' => $ref]);
        } catch (ReferenceNotFound $e) {
            return api_response($request, null, 500, ['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request, Referrals $referrals)
    {
        try {
            $this->validate($request, [
                'name'   => 'required|string',
                'mobile' => 'required|string|mobile:bd',
            ]);
            $referrals::getReference($request->partner)->store($request);
            return api_response($request, null, 200);

        } catch (AlreadyReferred $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (AlreadyExistProfile $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function getReferralFaqs(Request $request)
    {
        try {

            $faqs = array(
                array(
                    'question' => 'sManager রেফার কি?',
                    'answer'   => 'আপনার ব্যবসায়ী বন্ধুকে sManager অ্যাপ ব্যবহার করতে পরামর্শ দেয়াই হচ্ছে sManager রেফার। একি সাথে আপনি রেফার করে আয় করতে পারবেন।'
                ),
                array(
                    'question' => 'রেফার এ সর্বোচ্চ কত টাকা আয় করতে পারবেন?',
                    'answer'   => 'প্রতিটা রেফার থেকে আপনি সর্বোচ্চ ৬০ টাকা এবং যত খুশি তত রেফার করে আয় করতে পারবেন।'
                ),
                array(
                    'question' => 'কিভাবে রেফার করবেন?',
                    'answer'   => 'আপনার ব্যবসায়ী বন্ধুকে SMS করে অথবা লিঙ্ক শেয়ার করে রেফার করতে পারবেন।'
                ),
                array(
                    'question' => 'রেফারের টাকা কিভাবে পাবেন?',
                    'answer'   => 'প্রতি ধাপ শেষ হওয়ার পরে ঐ ধাপের নির্ধারিত টাকা আপনার ওয়ালেট এ জমা হয়ে যাবে।'
                ),
                array(
                    'question' => 'কোন ফিচার ব্যবহার করলে ঐ দিনকে ব্যবহৃত দিন বলে ধরা হবে?',
                    'answer'   => 'বেচা বিক্রি, স্টক, হিসাব খাতা, মার্কেটিং ও প্রোমো, বিক্রির খাতা, ডিজিটাল কালেকশন, বাকির খাতা।'
                )

            );
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getReferralSteps(Request $request)
    {
        try {
            $stepDetails          = collect(config('partner.referral_steps'))->where('visible',true)
                ->map(function ($item) {
                    return [
                        'ধাপ'          => $item['step'],
                        'আপনার আয়'     => convertNumbersToBangla($item['amount'], true, 0),
                        'কিভাবে করবেন' => $item['details']
                    ];
                });
            $data['steps']        = $stepDetails;
            $data['total_income'] = convertNumbersToBangla(collect(config('partner.referral_steps'))->sum('amount'), true, 0);
            return api_response($request, $stepDetails, 200, ['data' => $data]);

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }


}
