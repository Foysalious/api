<?php namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Referral\Exceptions\AlreadyExistProfile;
use Sheba\Referral\Exceptions\AlreadyReferred;
use Sheba\Referral\Exceptions\InvalidFilter;
use Sheba\Referral\Exceptions\ReferenceNotFound;
use Sheba\Referral\Referrals;
use Throwable;

class PartnerReferralController extends Controller
{

    public function index(Request $request, Referrals $referrals)
    {
        try {
            $partner       = $request->partner;
            $reference     = $referrals::getReference($partner);
            $refers        = $reference->getReferrals($request);
            $income        = $reference->totalIncome($request);
            $total_sms     = $reference->totalRefer();
            $total_success = $reference->totalSuccessfulRefer();
            return api_response($request, $reference->refers, 200, [
                'data' => [
                    'refers'        => $refers,
                    'total_income'  => $income,
                    'total_sms'     => $total_sms,
                    'total_success' => $total_success
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

    public function referLinkGenerate() { }

    public function earnings() { }

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

    public function getReferralFaqs(Request $request){
        try{

            $faqs = array(
                array(
                    'question' => 'রেফারেল কী?',
                    'answer' => array('রেফারেল হলো sManager অ্যাপ ব্যবহার করে আয় করার একটি পদ্ধতি। যেখানে আপনি আপনার বন্ধুকে sManager অ্যাপটি ব্যবহারে সহযোগিতা করবেন।')
                ),
                array(
                    'question' => 'রেফার করে কিভাবে আয় করবো?',
                    'answer' => array('আপনার বন্ধু যদি আপনার পাঠানো রেফারেল লিংক থেকে sManager অ্যাপটি ডাউনলোড করে এবং ব্যবহার শুরু করে তাহলেই আপনার আয় শুরু হয়ে যাবে। অ্যাপটি ব্যবহারে ১ম থেকে ৫ম ধাপ পর্যন্ত প্রতিটি ধাপে আপনি আপনার সেবা ক্রেডিট ব্যালেন্সে টাকা পেতে থাকবেন। সর্বোচ্চ একটি সফল রেফারেল থেকে ৫০০ টাকা পর্যন্ত আয়ের সুযোগ রয়েছে।')
                ),
                array(
                    'question' => 'কিভাবে রেফার করবো?',
                    'answer' => array('আপনার sManager অ্যাপ-এ ঢুকে বাম পাশের ম্যানেজার টুলস-এ যান। রেফার করুন অপশনে ঢুকে লিংকটি ফোনবুক কিংবা বিভিন্ন মাধ্যমে শেয়ার করতে পারবেন।')
                ),
                array(
                    'question' => 'আয় করার ধাপ গুলো কী কী এবং কোন ধাপে কত টাকা পাবো?',
                    'answer' => array(
                        array('আয়ের মোট ৫ টি ধাপ রয়েছে।'),
                        array('১ম ধাপে আপনার রেফার করা বন্ধু যদি sManager অ্যাপটি ৬ দিন ব্যবহার করে আপনি আপনার sManager সেবা ক্রেডিট-এ পেয়ে যাবেন ১০০ টাকা।'),
                        array('২য় ধাপে আপনার রেফার করা বন্ধু যদি sManager অ্যাপটি মোট ১২ দিন ব্যবহার করে আপনি আপনার sManager সেবা ক্রেডিট-এ পেয়ে যাবেন আরও ১০০ টাকা।'),
                        array('৩য় ধাপে আপনার রেফার করা বন্ধুকে sManager অ্যাপটি মোট ২৫ দিন ব্যবহার করতে হবে তাহলে  আপনি আপনার sManager সেবা ক্রেডিট-এ পেয়ে যাবেন আরও ১০০ টাকা।'),
                        array('৪র্থ ধাপে আপনার রেফার করা বন্ধুকে sManager অ্যাপটি মোট ৫০ দিন ব্যবহার করতে হবে তাহলে  আপনি আপনার sManager সেবা ক্রেডিট-এ পেয়ে যাবেন আরও ১০০ টাকা।'),
                        array('৫ম ধাপে আপনার বন্ধুকে sManager অ্যাপ-এর মাধ্যমে NID ভেরিফিকেশন করতে হবে, ভেরিফিকেশন করা হলে আপনি আপনার sManager সেবা ক্রেডিট-এ পেয়ে যাবেন আরও ১০০ টাকা।'),
                        array('এভাবে রেফারেলের মাধ্যমে ধাপে ধাপে সর্বমোট ৫০০ টাকা পর্যন্ত আয় করতে পারবেন।')
                    )
                ),
                array(
                    'question' => 'রেফারেল-এর টাকা সেবা ক্রেডিট থেকে কিভাবে উত্তোলন করবো?',
                    'answer' => array('রেফারেল-এর টাকা sManager সেবা ক্রেডিট ব্যালেন্স থেকে আপনি যেকোনো সময় বিকাশ অথবা ব্যাংকের মাধ্যমে উত্তোলন করতে পারবেন।')
                )

            );
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);

        }catch (\Throwable $e){
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getReferralSteps(Request $request){
        try{
            $stepDetails = collect(config('partner.referral_steps'))
                ->map(function($item) {
                    return [
                        'ধাপ' => $item['step'],
                        'আপনার আয়' => $item['amount'],
                        'কিভাবে করবেন' => $item['details']
                    ];
                });
            return api_response($request, $stepDetails, 200, ['steps' => $stepDetails]);

        }catch (\Throwable $e){
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }


}
