<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Mail;

class CareerController extends Controller
{
    public function apply(Request $request)
    {
        $slug = str_slug($request->input('name'), '_');
        Mail::raw($request->input('description'), function ($m) use ($request, $slug) {
            $m->from($request->input('email'), $request->input('name'));
            $m->to('pasha.startern@gmail.com');
            $m->subject('RESUME FOR ' . $request->input('jobTitle'));
            $m->attachData(file_get_contents($request->file('file')), 'RESUME_' . $slug . '.pdf');
        });
        return response(['msg' => 'ok', 'code' => 200]);
    }
}
