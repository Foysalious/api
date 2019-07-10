<?php namespace App\Http\Controllers\Profile;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function getInfo(Request $request)
    {
        try {
            return api_response($request, true, 200, ['data' => [
                'is_registered' => $request->is_registered ? (int)$request->is_registered : 0,
                'has_password' => $request->has_password ? (int)$request->has_password : 0,
                'has_partner' => $request->has_partner ? (int)$request->has_partner : 0,
                'has_resource' => $request->has_resource ? (int)$request->has_resource : 0,
            ]]);
        } catch (ValidationException $e) {
            return api_response($request, null, 401, ['message' => 'Invalid mobile number']);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPartnerInfo(Request $request)
    {
        try {
            return api_response($request, true, 200, [
                'data' => [
                    'partner' => [
                        'id' => 1,
                        'name' => 'adad',
                        'mobile' => '+88017589',
                        'address' => 'afaf',
                        'geo' => [
                            'lat' => 455,
                            'lng' => 47,
                            'radius' => 5
                        ],
                        'categories' => [
                            ['id' => 4, 'name' => 'ad'],
                            ['id' => 5, 'name' => 'af'],
                            ['id' => 6, 'name' => 'aafafd'],
                        ]
                    ],
                    'resource' => [
                        'id' => 1,
                        'name' => 'adad',
                        'token' => str_random(30)
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}