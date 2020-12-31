<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController
{
    /**
     * @SWG\Swagger(
     *     schemes={"http"},
     *     host="api.sheba.test",
     *     basePath="/",
     *     @SWG\Info(
     *         version="1.0.0",
     *         title="Swagger Integration with PHP Laravel",
     *         description="Integrate Swagger in Laravel application",
     *         termsOfService="",
     *         @SWG\Contact(
     *             email="sachit.wadhawan@quovantis.com"
     *         ),
     *     ),
     * )
     */
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;
}
