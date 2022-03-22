<?php

namespace App\Sheba\DynamicForm;

use App\Models\District;
use App\Models\Division;
use App\Models\Partner;
use Sheba\Dal\MefForm\Model as MefForm;
use Sheba\Dal\MefSections\Model as MefSection;
use Sheba\MerchantEnrollment\MerchantEnrollmentFileHandler;

class DynamicForm
{
    /*** @var MefForm */
    private $form;

    /*** @var MefSection */
    private $section;

    /*** @var Partner */
    private $partner;

    private $requestData;
    private $type;

    public function setForm($form_id): DynamicForm
    {
        $this->form = MefForm::find($form_id);
        return $this;
    }

    public function getFormSections(): array
    {
        $categories = array();
        foreach ($this->form->sections as $section) {
            $this->setSection($section->id);
            $fields = $this->getSectionFields();
            $completion = (new CompletionCalculation())->setFields($fields)->calculate();

            $categories[] = (new CategoryDetails())->setCategoryCode($section->key)
                ->setCompletionPercentage($completion)->setCategoryId($section->id)
                ->setName($section->name, $section->bn_name)->toArray();
        }
        return ["category_list" => $categories];
    }

    public function getSectionDetails(): array
    {
        return [
            "name" => $this->getSectionNames(),
            "fields" => $this->getSectionFields(),
            "post_url" => $this->section->post_url
        ];
    }

    /**
     * @return void
     * @throws Exceptions\FormValidationException
     */
    public function postSectionFields()
    {
        (new FormValidator())->setFields($this->section->fields)->setPostData($this->requestData)->validate();
        (new FormSubmit())->setPartner($this->partner)->setFields($this->section->fields)->setPostData($this->requestData)->store();
    }

    private function getSectionNames()
    {
        return (new CategoryDetails())->setName($this->section->name, $this->section->bn_name)->getName();
    }

    public function getSectionFields(): array
    {
        $fields = array();
        $form_builder = (new FormFieldBuilder())->setPartner($this->partner);
        foreach ($this->section->fields as $field)
            $fields[] = $form_builder->setField($field)->build()->toArray();
        dd($fields);
        return $fields;
    }

    /**
     * @param mixed $partner
     * @return DynamicForm
     */
    public function setPartner($partner): DynamicForm
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $section_id
     * @return DynamicForm
     */
    public function setSection($section_id): DynamicForm
    {
        $this->section = MefSection::find($section_id);
        return $this;
    }

    /**
     * @param mixed $requestData
     * @return DynamicForm
     */
    public function setRequestData($requestData): DynamicForm
    {
        $this->requestData = $requestData;
        return $this;
    }

    /**
     * @param $type
     * @return DynamicForm
     */
    public function setType($type): DynamicForm
    {
        $this->type = $type;
        return $this;
    }


    public function typeData($request)
    {
        if ($this->type == "division") {
            return Division::get();
        }
        if ($this->type == "district") {
            if ($request->division)
                return District::where('division_id', $request->division)->get();
            return District::get();
        }
        if ($this->type == "tradeLicenseExist") {
            return config('trade_license.data');
        }
    }


    public function uploadDocumentData($document, $document_id)
    {
        $field = $this->section->fields->where('name', $document_id)->first();
        $url = (new MerchantEnrollmentFileHandler())->setPartner($this->partner)->uploadDocument($document, json_decode($field->data, true))->getUploadedUrl();
        $dynamic_field = json_decode($field->data, true);
        $dynamic_field['data'] = $url;
        $field->data = json_encode($dynamic_field);
        return $field->save();
    }
}
