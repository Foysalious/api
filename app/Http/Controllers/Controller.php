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
     *         title="Sheba API Project",
     *         description="Shbea API documentation",
     *         termsOfService="",
     *         @SWG\Contact(
     *             email="info@sheba.xyz"
     *         ),
     *     ),
     * )
     */
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;
}
