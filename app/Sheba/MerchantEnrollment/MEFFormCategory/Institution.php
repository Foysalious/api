<?php

namespace Sheba\MerchantEnrollment\MEFFormCategory;

use Sheba\MerchantEnrollment\InstitutionInformation;
use Sheba\MerchantEnrollment\Statics\FormStatics;

class Institution extends MEFFormCategory
{
    protected $category_code = 'institution';

    public function completion(): array
    {
        return [
            'en' => 100,
            'bn' => 100
        ];
    }

    public function get(): CategoryGetter
    {
        $formItems = $this->getStaticFormData();
        $formData  = (new InstitutionInformation())->setPartner($this->partner)->setFormItems($formItems)->getByCode($this->category_code);
        return $this->getFormData($formItems, $formData);
    }

    public function post($data)
    {
        $formItems = FormStatics::institution();
        (new InstitutionInformation())->setPartner($this->partner)->setFormItems($formItems)->postByCode($this->category_code, $data);
    }

    public function getStaticFormData()
    {
        return FormStatics::institution();
    }
}