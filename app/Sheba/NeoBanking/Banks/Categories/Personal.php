<?php namespace Sheba\NeoBanking\Banks\Categories;

use Sheba\NeoBanking\Banks\BankFormCategoryFactory;
use Sheba\NeoBanking\Banks\CategoryGetter;
use Sheba\NeoBanking\Banks\Completion;
use Sheba\NeoBanking\Banks\CompletionCalculation;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\DTO\FormItemBuilder;
use Sheba\NeoBanking\Statics\FormStatics;

class Personal extends BankFormCategory
{
    protected $code = 'personal';


    public function completion()
    {
        return [
            'en' => $this->percentageCalculation(),
            'bn' => $this->getBengaliPercentage()
        ];
    }

    public function get(): CategoryGetter
    {
        $nid_done = 0;
        $formItems = FormStatics::personal();
        $nid_class = app()->make(NIDSelfie::class);
        $nid_class->setBank($this->bank);
        $nid = (new CategoryGetter())->setCategory($nid_class)->toArray();

        if(isset($nid) && $nid['completion']['en'] == 100) $nid_done = 1;

        if(!$nid_done) {
            $banner = FormStatics::dynamic_banner();
            $formItems = array_merge($banner, $formItems);
        }
        return $this->getFormData($formItems);
    }

    public function post($data)
    {
        return !!$this->bankAccountData->postByCode($this->code, $data);
    }

    public function getLastUpdated()
    {
        $this->setLastUpdated();
        return $this->last_updated;
    }

    public function getDummy()
    {

    }

    public function percentageCalculation()
    {
        if (!isset($this->data)) {
            $formItems = FormStatics::personal();
            $this->bank->loadInfo();
            $this->setBankAccountData($this->bank->getBankInfo());
            $this->getFormData($formItems);
        }
        $this->percentage = (new CompletionCalculation())->get($this->data);
        $this->percentage = round($this->percentage);
        return $this->percentage;
    }
}
