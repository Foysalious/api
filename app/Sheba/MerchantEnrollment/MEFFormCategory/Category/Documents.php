<?php

namespace Sheba\MerchantEnrollment\MEFFormCategory\Category;

use App\Sheba\MerchantEnrollment\PersonalInformation;
use Sheba\MerchantEnrollment\DocumentInformation;
use Sheba\MerchantEnrollment\MEFFormCategory\CategoryGetter;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;
use Sheba\MerchantEnrollment\Statics\FormStatics;

class Documents extends MEFFormCategory
{
    public $category_code = 'documents';

    public function completion(): array
    {
        return [
            'en' => $this->percentageCalculation(),
            'bn' => $this->getBengaliPercentage()
        ];
    }

    public function get(): CategoryGetter
    {
        $formData  = $this->getFormFieldData();
        return $this->getFormData($this->getFormFields(), $formData);
    }

    public function post($data)
    {
        $formItems = $this->getFormFields();
        (new DocumentInformation())->setPartner($this->partner)->setFormItems($formItems)->postByCode($this->category_code, $data);
    }

    public function getFormFields()
    {
        $form_fields = FormStatics::documents();
        if(count($this->exclude_form_keys)) {
            foreach ($form_fields as $key => $item) {
                if(in_array($item['id'], $this->exclude_form_keys))
                    unset($form_fields[$key]);

            }
        }
        return $form_fields;
    }

    public function getFormFieldData(): array
    {
        $formItems = $this->getFormFields();
        return (new DocumentInformation())->setPartner($this->partner)->setFormItems($formItems)->getByCode($this->category_code);
    }
}