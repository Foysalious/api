<?php namespace App\Http\Middleware;


class JwtAccessTokenMiddleware extends AccessTokenMiddleware
{
    protected function formApiResponse($request, $internal, $code, array $data)
    {
        return http_response($request, $internal, $code, $data);
    }

    protected function setExtraDataToRequest($request)
    {
        $manager_resource = $request->auth_user->getResource();
        $partner = $request->auth_user->getPartner();
        if ($manager_resource && $partner) {
            if ($manager_resource->isManager($partner)) {
                $request->merge(['manager_resource' => $manager_resource]);
            }
        }
    }

}