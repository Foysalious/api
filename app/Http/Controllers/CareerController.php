<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Validator;

class CareerController extends Controller
{
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'file' => 'required|file',
            'cover' => 'required|file'
        ]);
        if ($validator->fails()) {
            return response()->json(['msg' => 'validation fail!', 'code' => 500]);
        }
        $cv = $request->file('file');
        $cover = $request->file('cover');

        Mail::raw($request->input('description'), function ($m) use ($request, $cv, $cover) {
            $m->from($request->input('email'), $request->input('name'));
            $m->to('career@sheba.xyz');
            $m->subject($request->input('jobTitle'));
            $m->attachData(file_get_contents($cv), 'Resume - ' . $request->input('name') . '.' . $cv->extension());
            $m->attachData(file_get_contents($cover), 'Cover letter - ' . $request->input('name') . '.' . $cover->extension());
        });
        return response()->json(['msg' => 'ok', 'code' => 200]);
    }

    public function getVacantPosts(Request $request)
    {
        $job_posts = Career::select("id", "job_title", "vacancy", "requirements", "educational_requirements", "additional_requirements",
            "job_nature", "location", "salary", "experience", "benefits", "note", "additional_info", "deadline")
            ->where([
                ['deadline', '>=', date('Y-m-d')],
//                ['publication_status', 1]
            ])->get();
        if ($job_posts) {
            return api_response($request, $job_posts, 200, ['posts' => $job_posts]);
        } else {
            return api_response($request, $job_posts, 404);
        }
    }
}
