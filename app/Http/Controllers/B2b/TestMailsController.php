<?php namespace App\Http\Controllers\B2b;

use App\Exceptions\MailgunClientException;
use App\Http\Controllers\Controller;
use App\Jobs\Business\SendTestMail;
use App\Jobs\SendBusinessRequestEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Exception;
use Sheba\Repositories\ProfileRepository;
use Throwable;

class TestMailsController extends Controller
{

    private $profileRepository;

    public function __construct(ProfileRepository $profile_repository)
    {
        $this->profileRepository = $profile_repository;
    }

    public function testMail(Request $request)
    {
        config()->set('services.mailgun.domain', config('services.mailgun.business_domain'));
        #$this->dispatch(new SendTestMail());
        #return api_response($request, null, 200);
        $email = $request->email;
        $subject = "This Is Test Mail";
        if ($request->has('design') && $request->design == 1) {
            Mail::send('emails.test-mail', [], function ($m) use ($email, $subject) {
                $m->to($email)->subject($subject);
            });
        } elseif ($request->has('complex_design') && $request->complex_design == 1) {
            Mail::send('emails.email_verification_V3', ['code' => 1111], function ($m) use ($email, $subject) {
                $m->to($email)->subject($subject);
            });
        } elseif ($request->has('invite') && $request->invite == 1) {
            try {
                $profile = $this->profileRepository->checkExistingEmail($email);
                $coworker_invite_email = new SendBusinessRequestEmail($email);
                $password = str_random(6);
                $this->profileRepository->updateRaw($profile, ['password' => bcrypt($password)]);
                $coworker_invite_email->setPassword($password);

                $coworker_invite_email->setSubject("Invitation from your co-worker to join digiGO")->setTemplate('emails.co-worker-invitation-v3');
                dispatch($coworker_invite_email);
            } catch (Throwable $e) {
                app('sentry')->captureException($e);
            }

        } else {
            Mail::send([], [], function ($m) use ($email, $subject) {
                $m->to($email)->subject($subject)->setBody('Hi, welcome to Sheba Platform Limited.');
            });
        }

        return api_response($request, null, 200);
    }

}