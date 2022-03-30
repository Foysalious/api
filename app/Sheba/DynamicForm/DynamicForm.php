<?php

namespace App\Sheba\DynamicForm;

use App\Models\District;
use App\Models\Division;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\MefForm\Model as MefForm;
use Sheba\Dal\MefSections\Model as MefSection;
use Sheba\MerchantEnrollment\MerchantEnrollmentFileHandler;
use Sheba\MerchantEnrollment\Statics\PaymentMethodStatics;

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
    private $formKey;

    /**
     * @var PartnerMefInformation
     */
    private $partnerMefInformation;

    public function setForm(): DynamicForm
    {
        $this->form = MefForm::where('key',$this->formKey)->first();
        return $this;
    }

    public function __construct(PartnerMefInformation $partnerMefInformation)
    {
        $this->partnerMefInformation = $partnerMefInformation;
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
                ->setTitle($section->name, $section->bn_name)->toArray();

        }
//        (new SectionListResponse())->setCategoryList($categories)
        return ["categories" => $categories, "can_apply" => 0, "overall_completion" => ["en"=>95, "bn" => "৯৫"], "message" => [PaymentMethodStatics::mtbCompletionPageMessage()]];
    }

    public function getSectionDetails(): array
    {
        return [
            "title" => $this->getSectionNames(),
            "form_items" => $this->getSectionFields(),
            "post_url" => $this->getPostUrl()
        ];
    }

    private function getPostUrl(): string
    {
        return config('sheba.api_url').$this->section->post_url;
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
        return (new CategoryDetails())->setTitle($this->section->name, $this->section->bn_name)->getTitle();
    }

    public function getSectionFields(): array
    {
        $fields = array();
        $form_builder = (new FormFieldBuilder())->setPartner($this->partner);
        foreach ($this->section->fields as $field)
            $fields[] = $form_builder->setField($field)->build()->toArray();
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

    /**
     * @param mixed $formKey
     * @return DynamicForm
     */
    public function setFormKey($formKey): DynamicForm
    {
        $this->formKey = $formKey;
        $this->setForm();
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
        $field = DB::table('fields')->where('data->id', $document_id)->first();
        $url = (new MerchantEnrollmentFileHandler())->setPartner($this->partner)->uploadDocument($document, json_decode($field->data, true))->getUploadedUrl();
        $this->partnerMefInformation->setProperty(json_decode($this->partner->partnerMefInformation->partner_information));
        $this->partnerMefInformation->$document_id = $url;
        $this->partner->partnerMefInformation->partner_information = json_encode($this->partnerMefInformation->getAvailable());
        return $this->partner->partnerMefInformation->save();
    }
}
