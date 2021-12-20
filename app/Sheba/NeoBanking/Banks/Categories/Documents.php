<?php namespace Sheba\NeoBanking\Banks\Categories;

use App\Repositories\FileRepository;
use Sheba\NeoBanking\Banks\CategoryGetter;
use Sheba\NeoBanking\Banks\CompletionCalculation;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\Statics\FormStatics;

class Documents extends BankFormCategory
{
    protected $code = 'documents';

    public function completion()
    {
        return [
            'en' => $this->percentageCalculation(),
            'bn' => $this->getBengaliPercentage()
        ];
    }

    public function get() :CategoryGetter
    {
        $formItems = FormStatics::documents();
        return $this->getFormData($formItems);
    }

    public function post($data)
    {
        $formData  = (array)$this->bankAccountData->getByCode($this->code);
        if(empty($formData))
            return !!$this->bankAccountData->postByCode($this->code, $data);
        $this->checkExistingImage($formData, $data);
        $data = array_merge($formData, $data);
        unset($data['updated_at']);
        return !!$this->bankAccountData->postByCode($this->code, $data);
    }

    private function checkExistingImage($formData, $data)
    {
        foreach ($formData as $key => $url)
        {
            if(isset($data[$key])) $this->deleteOld($url);
        }
    }

    public function getLastUpdated()
    {
        $this->setLastUpdated();
        return $this->last_updated;
    }

    public function getDummy()
    {
        // TODO: Implement getDummy() method.
    }

    private function deleteOld($image)
    {
        if (basename($image) != 'default.jpg') {
            $filename = substr($image, strlen(config('sheba.s3_url')));
            self::deleteOldImage($filename);
        }
    }

    public static function deleteOldImage($filename)
    {
        /** @var FileRepository $fileRepository */
        $fileRepository = app(FileRepository::class);
        $fileRepository->deleteFileFromCDN($filename);
    }

    public function percentageCalculation()
    {
        if (!isset($this->data)) {
            $formItems = FormStatics::documents();
            $this->bank->loadInfo();
            $this->setBankAccountData($this->bank->getBankInfo());
            $this->getFormData($formItems);
        }
        $this->percentage = (new CompletionCalculation())->get($this->data);
        $this->percentage = round($this->percentage);
        return $this->percentage;
    }

}
