<?php namespace App\Http\Controllers\B2b;

use App\Exceptions\MailgunClientException;
use App\Http\Controllers\Controller;
use App\Jobs\Business\SendTestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Exception;

class TestMailsController extends Controller
{
    public function testMail(Request $request)
    {
        #$this->dispatch(new SendTestMail());
        #return api_response($request, null, 200);
        $email = $request->email;
        $subject = "This Is Test Mail";
        if ($request->has('design') && $request->design == 1) {
            Mail::send('emails.co-worker-invitation-v2', ['email' => 'miajee@sheba.xyz', 'password' => 123456], function ($m) use ($email, $subject) {
                $m->to($email)->subject($subject);
            });
        } elseif ($request->has('complex_design') && $request->complex_design == 1) {
            Mail::send('emails.email_verification_V3', ['code' => 1111], function ($m) use ($email, $subject) {
                $m->to($email)->subject($subject);
            });
        } else {
            Mail::send([], [], function ($m) use ($email, $subject) {
                $m->to($email)->subject($subject)->setBody('Hi, welcome to Sheba Platform Limited.');
            });
        }

        return api_response($request, null, 200);
    }

}