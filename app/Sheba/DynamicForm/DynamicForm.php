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
        $this->form = MefForm::where('key', $this->formKey)->first();
        return $this;
    }

    public function __construct(PartnerMefInformation $partnerMefInformation)
    {
        $this->partnerMefInformation = $partnerMefInformation;
    }

    public function getFormSections(): array
    {
        $categories = $this->sectionDetails();
        $finalCompletion = (new CompletionCalculation())->getFinalCompletion($categories);

        return (new SectionListResponse())->setCategories($categories)
            ->setMessage(PaymentMethodStatics::dynamicCompletionPageMessage($this->formKey))
            ->setOverallCompletion($finalCompletion)->setCanApply($finalCompletion)->toArray();
    }

    private function sectionDetails(): array
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
        return $categories;
    }

    public function getSectionDetails(): array
    {
        $fields = $this->getSectionFields();
        return [
            "title" => $this->getSectionNames(),
            "form_items" => $fields,
            "completion" => $this->getSectionCompletion($fields),
            "post_url" => $this->getPostUrl()
        ];
    }

    private function getSectionCompletion($fields): array
    {
        $completion = (new CompletionCalculation())->setFields($fields)->calculate();
        return [
            "en" => $completion,
            "bn" => convertNumbersToBangla($completion, false),
        ];
    }

    private function getPostUrl(): string
    {
        return config('sheba.api_url') . $this->section->post_url;
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
            $division = Division::get();
            $division = (new CollectionFormatter())->setData($division)->formatCollection();
            $data = [
                'division' => ['list' => $division]
            ];
            return $data['division'];
        }
        if ($this->type == "district") {
            if ($request->division) {
                $district = District::where('division_id', $request->division)->get();
                $district = (new CollectionFormatter())->setData($district)->formatCollection();
                $data = [
                    'district' => ['list' => $district]
                ];
                return $data['district'];
            }
            $district = District::get();
            $district = (new CollectionFormatter())->setData($district)->formatCollection();
            $data = [
                'district' => ['list' => $district]
            ];
            return $data['district'];
        }
        if ($this->type == "tradeLicenseExists") {
            return config('trade_license');
        }
    }


    public function uploadDocumentData($document, $document_id)
    {
        $field = DB::table('fields')->where('data->id', $document_id)->first();
        $url = (new MerchantEnrollmentFileHandler())->setPartner($this->partner)->uploadDocument($document, json_decode($field->data, true))->getUploadedUrl();
        (new FormSubmit())->setPartner($this->partner)->setFields($field)->documentStore($url);
    }
}
