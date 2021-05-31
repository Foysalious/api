<?php namespace Sheba\FileManagers;

use App\Sheba\FileManagers\PartnerChequeBookReceipt;
use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileManager
{
    protected function makeBanner($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new Banner($file))->make();
        return [$file, $filename];
    }

    protected function uniqueFileName($file, $name, $ext = null)
    {
        if (empty($name)) {
            $name = "TIWNN";
        }
        $name = strtolower(str_replace(' ', '_', $name));
        return time() . "_" . $name . "." . ($ext ?: $this->getExtension($file));
    }

    private function getExtension($file)
    {
        if ($file instanceof UploadedFile) return $file->getClientOriginalExtension();
        if ($file instanceof Image) return explode('/', $file->mime())[1];
        return getBase64FileExtension($file);
    }

    protected function makeAppBanner($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new AppBanner($file))->make();
        return [$file, $filename];
    }

    protected function makeThumb($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new Thumb($file))->make();
        return [$file, $filename];
    }

    protected function makeAppThumb($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new AppThumb($file))->make();
        return [$file, $filename];
    }

    protected function makeIcon($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new Icon($file))->make();
        return [$file, $filename];
    }

    protected function makeSlide($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new Slide($file))->make();
        return [$file, $filename];
    }

    protected function makeBankStatement($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new BankStatement($file))->make();
        return [$file, $filename];
    }

    protected function makeChequeBookReceipt($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new PartnerChequeBookReceipt($file))->make();
        return [$file, $filename];
    }

    protected function makeTradeLicense($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new TradeLicense($file))->make();
        return [$file, $filename];
    }

    protected function makeLoanFile($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        return [$file, $filename];
    }

    protected function makeNeoBankingFile($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        return [$file, $filename];
    }

    protected function makeVatRegistration($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new TradeLicense($file))->make();
        return [$file, $filename];
    }

    protected function makeAppSlide($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new AppSlide($file))->make();
        return [$file, $filename];
    }

    protected function makePushNotificationIcon($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new PushIcon($file))->make();
        return [$file, $filename];
    }

    protected function makePushNotificationImage($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new PushImage($file))->make();
        return [$file, $filename];
    }

    protected function makePosServiceAppThumb($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new PosServiceAppThumb($file))->make();
        return [$file, $filename];
    }

    protected function makeTradeLicenseDocument($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        return [$file, $filename];
    }

    protected function makeVatRegistrationDocument($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        return [$file, $filename];
    }

    protected function makeProPic($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        return [$file, $filename];
    }

    protected function makeAttachment($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        return [$file, $filename];
    }

    protected function makePartnerProofOfBusiness($file, $name)
    {
        return [$file, $this->uniqueFileName($file, $name)];
    }

    protected function makeImageGallery($file, $name)
    {
        return [$file, $this->uniqueFileName($file, $name)];
    }

    protected function makeAppVersionImage($file, $name)
    {
        $filename = $this->uniqueFileName($file, $name);
        $file = (new AppVersionImage($file))->make();
        return [ $file, $filename ];
    }
}
