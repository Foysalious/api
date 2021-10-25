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
     *     schemes={"http","https"},
     *     host="api.dev-sheba.xyz",
     *     basePath="/",
     *     @SWG\Info(
     *         version="1.0.0",
     *         title="Sheba API Project",
     *         description="php artisan l5-swagger:generate || php artisan swagger-upload-json",
     *         termsOfService="smanager.sheba.xyz",
     *         @SWG\Contact(
     *             email="info@sheba.xyz"
     *         ),
     *     ),
     * )
     */
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
