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
        try {
            $email = $request->email;
            $subject = "This Is Test Mail";
            Mail::send([], [], function ($m) use ($email, $subject) {
                $m->to($email)->subject($subject)->setBody('Hi, welcome to Sheba Platform Limited.');
            });
        } catch (Exception $e) {
            throw new MailgunClientException();
        }
    }

}