<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class CareerController extends Controller
{
    public function apply(Request $request)
    {
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
}
