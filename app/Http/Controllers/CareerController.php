<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Validator;

class CareerController extends Controller
{
    public function apply(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
                'file' => 'required|file',
                'cover' => 'required|file'
            ]);
            $cv = $request->file('file');
            $cover = $request->file('cover');

            Mail::raw($request->input('description'), function ($m) use ($request, $cv, $cover) {
                $m->from($request->email, $request->name);
                $m->to('career@sheba.xyz');
                $m->subject($request->input('jobTitle'));
                $m->attachData(file_get_contents($cv), 'Resume - ' . $request->name . '.' . $cv->extension());
                $m->attachData(file_get_contents($cover), 'Cover letter - ' . $request->name . '.' . $cover->extension());
            });
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getVacantPosts(Request $request)
    {
        try {
            $job_posts = Career::select("id", "job_title", "vacancy", "requirements", "educational_requirements", "additional_requirements",
                "job_nature", "location", "salary", "experience", "benefits", "note", "additional_info", "deadline")
                ->where([
                    ['deadline', '>=', date('Y-m-d')],
                    ['publication_status', 1]
                ])->get();
            if (count($job_posts) > 0) {
                return api_response($request, $job_posts, 200, ['posts' => $job_posts]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}
