<?php

use App\Http\Requests\ApiRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Helpers\Http\ShebaRequestHeader;
use Sheba\Helpers\Http\ShebaResponse;
use Sheba\Portals\Portals;

if (!function_exists('api_response')) {
    /**
     * @param            $request
     * @param            $internal_response
     * @param            $response_code
     * @param array|null $external_response
     * @return JsonResponse
     */
    function api_response($request, $internal_response, $response_code, array $external_response = null)
    {

        $public_response = (new ShebaResponse)->$response_code;
        if ($external_response != null) {
            $public_response = array_merge($public_response, $external_response);
        }
        if (class_basename($request) == 'Request' || $request instanceof ApiRequest) {
            return response()->json($public_response);
        } else {
            return $internal_response;
        }
    }
}

if (!function_exists('calculatePagination')) {
    /**
     * @param $request
     * @return array
     */
    function calculatePagination($request)
    {
        $offset = $request->has('offset') ? $request->offset : 0;
        $limit  = $request->has('limit') ? $request->limit : 50;
        return [$offset, $limit];
    }
}

if (!function_exists('calculatePaginationNew')) {
    /**
     * @param $request
     * @return array
     */
    function calculatePaginationNew($request)
    {
        $page  = $request->has('page') ? $request->page : 0;
        $limit = $request->has('limit') ? $request->limit : 50;
        return [$page, $limit];
    }
}

if (!function_exists('calculateSort')) {
    /**
     * @param        $request
     * @param string $default
     * @return array
     */
    function calculateSort($request, $default = 'id')
    {
        $offset = $request->has('sort') ? $request->sort : $default;
        $limit  = $request->has('sort_order') ? $request->sort_order : 'DESC';
        return [$offset, $limit];
    }
}

if (!function_exists('getValidationErrorMessage')) {
    /**
     * @param $errors
     * @return string
     */
    function getValidationErrorMessage($errors)
    {
        $msg = '';
        foreach ($errors as $error) {
            $msg .= $error;
        }
        return $msg;
    }
}

if (!function_exists('decodeGuzzleResponse')) {
    /**
     * @param      $response
     * @param bool $assoc
     * @return array
     */
    function decodeGuzzleResponse($response, $assoc = true)
    {
        return json_decode($response->getBody()->getContents(), $assoc);
    }
}

if (!function_exists('getUserTypeFromRequestHeader')) {
    /**
     * @param Request $request
     * @return string
     */
    function getUserTypeFromRequestHeader(Request $request)
    {
        return Portals::getUserTypeFromPortal($request->header('portal-name'));
    }
}

if (!function_exists('getShebaRequestHeader')) {
    /**
     * @return ShebaRequestHeader
     */
    function getShebaRequestHeader()
    {
        $request = \request();
        $header  = new ShebaRequestHeader();

        if ($request->hasHeader(ShebaRequestHeader::VERSION_CODE_KEY))
            $header->setVersionCode($request->header(ShebaRequestHeader::VERSION_CODE_KEY));

        if ($request->hasHeader(ShebaRequestHeader::PORTAL_NAME_KEY))
            $header->setPortalName($request->header(ShebaRequestHeader::PORTAL_NAME_KEY));

        if ($request->hasHeader(ShebaRequestHeader::PLATFORM_NAME_KEY))
            $header->setPlatformName($request->header(ShebaRequestHeader::PLATFORM_NAME_KEY));

        return $header;
    }
}

if (!function_exists('getIp')) {
    /**
     * @return string
     */
    function getIp()
    {
        $ip_methods = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($ip_methods as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); //just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return request()->ip();
    }
}
