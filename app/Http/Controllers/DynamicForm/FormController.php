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

    /**
     * @param Request $request
     * @param $form_id
     * @return JsonResponse
     */
    public function getSections(Request $request, $form_id): JsonResponse
    {
        $data = $this->dynamicForm->setForm($form_id)->getFormSections();
        return http_response($request, null, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @param $form_id
     * @param $section_id
     * @return JsonResponse
     */
    public function getSectionWiseFields(Request $request, $form_id, $section_id): JsonResponse
    {
        $partner = $request->auth_user->getPartner();
        $data = $this->dynamicForm->setForm($form_id)->setPartner($partner)->setSection($section_id)->getSectionDetails();
        return http_response($request, null, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @param $form_id
     * @param $section_id
     * @return JsonResponse
     */
    public function postSectionWiseFields(Request $request, $form_id, $section_id): JsonResponse
    {
        $partner = $request->auth_user->getPartner();
        $data = $this->dynamicForm->setForm($form_id)->setRequestData($request->data)->setPartner($partner)
            ->setSection($section_id)->postSectionFields();
        return http_response($request, null, 200, ['data' => $data]);
    }
}