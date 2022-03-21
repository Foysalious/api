<?php

namespace App\Http\Controllers\DynamicForm;

use App\Http\Controllers\Controller;
use App\Sheba\DynamicForm\DynamicForm;
use App\Sheba\DynamicForm\Exceptions\FormValidationException;
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
     * @throws FormValidationException
     */
    public function postSectionWiseFields(Request $request, $form_id, $section_id): JsonResponse
    {
        $this->validate($request, ["data" => "required|json"]);
        $partner = $request->auth_user->getPartner();
        $this->dynamicForm->setForm($form_id)->setRequestData($request->data)->setPartner($partner)
            ->setSection($section_id)->postSectionFields();
        return http_response($request, null, 200, ['message' => "Successful"]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function selectTypes(Request $request): JsonResponse
    {
        $this->validate($request, ["type" => "required"]);
        $data = $this->dynamicForm->setType($request->type)->typeData($request);
        return http_response($request, null, 200, ['message' => 'Successful', 'data' => $data]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $partner = $request->auth_user->getPartner();
        $data = $this->dynamicForm->uploadDocumentData($request,$partner);
        return http_response($request, null, 200, ['message' => 'Successful', 'data' => $data]);
    }
}
