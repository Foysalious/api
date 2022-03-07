<?php

namespace App\Http\Controllers\DynamicForm;

use App\Http\Controllers\Controller;
use App\Sheba\DynamicForm\DynamicForm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormController extends Controller
{
    /**
     * @var DynamicForm
     */
    private $dynamicForm;

    public function __construct(DynamicForm $form)
    {
        $this->dynamicForm = $form;
    }

    public function get(Request $request, $form_id): JsonResponse
    {
        $data = $this->dynamicForm->setFormId($form_id)->getFormCategory();
        return http_response($request, null, 200, ['data' => $data]);
    }
}