<?php

use App\Http\Requests\ApiRequest;
use Illuminate\Http\JsonResponse;

if (!function_exists('api_response')) {
    /**
     * @param $request
     * @param $internal_response
     * @param $response_code
     * @param array|null $external_response
     * @return JsonResponse
     */
    function api_response($request, $internal_response, $response_code, array $external_response = null)
    {
        $public_response = constants('API_RESPONSE_CODES')[$response_code];
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
        return [ $offset, $limit ];
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
        return [ $page, $limit ];
    }
}

if (!function_exists('calculateSort')) {
    /**
     * @param $request
     * @param string $default
     * @return array
     */
    function calculateSort($request, $default = 'id')
    {
        $offset = $request->has('sort') ? $request->sort : $default;
        $limit  = $request->has('sort_order') ? $request->sort_order : 'DESC';
        return [ $offset, $limit ];
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
     * @param $response
     * @return array
     */
    function decodeGuzzleResponse($response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }
}
