<?php

namespace Sheba\ComplianceInfo;

use App\Models\Partner;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;

class ComplianceInfo
{
    use CdnFileManager, FileManager, ModificationFields;

    private $partner;

    /**
     * @param mixed $partner
     * @return ComplianceInfo
     */
    public function setPartner(Partner $partner): ComplianceInfo
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->formatData();
    }

    private function formatData(): array
    {
        return [
            "shop_type" => $this->partner->basicInformations->shop_type ?? '',
            "monthly_transaction_volume" => $this->partner->basicInformations->monthly_transaction_volume ?? '',
            "registration_year" => Carbon::parse($this->partner->basicInformations->registration_year)->toDateString() ?? '',
            "email" => $this->partner->email ?? '',
            "bank_account" => $this->formatBankAccount($this->partner->withdrawalBankInformations()->first()),
            "trade_license" => $this->partner->basicInformations->trade_license ?? "",
            "tin_licence_photo" => $this->partner->basicInformations->tin_licence_photo ?? "",
            "electricity_bill_image" => $this->partner->basicInformations->electricity_bill_image ?? ""
        ];
    }

    /**
     * @param $account
     * @return array
     */
    private function formatBankAccount($account): array
    {
        return [
            "id" => $account->id,
            "purpose" => $account->purpose,
            "acc_no" => $account->acc_no,
            "acc_name" => $account->acc_name,
            "acc_type" => $account->acc_type,
            "bank_name" => $account->bank_name,
            "branch_name" => $account->branch_name,
            "routing_no" => $account->routing_no
        ];
    }

    public function updateData($data)
    {
        $data = $this->getNotNullKeys($data);
        if(isset($data['email'])) $this->updateEmail($data['email']);
        $data = array_except($data, 'email');

        if(isset($data['tin_licence_photo'])) $this->getImageUrl('tin_licence_photo', $data);

        if(isset($data['electricity_bill_image'])) $this->getImageUrl('electricity_bill_image',$data);

        $this->partner->basicInformations->update($this->withUpdateModificationField($data));
    }

    /**
     * @param $data
     * @return array
     */
    private function getNotNullKeys($data): array
    {
        $not_null_data = array();
        foreach ($data as $key=> $value)
            if($value !== null)
                $not_null_data[$key] = $value;
        return $not_null_data;

    }

    private function getImageUrl($key, &$data)
    {
        $image = $data[$key];
        $name = time()."_".$key."_".$image->getClientOriginalName();
        $image = $this->_saveDocumentImage($image, $name);
        $data[$key] = $image;
        if(isset($this->partner->basicInformations->$key)) {
            $filename = substr($this->partner->basicInformations->$key, strlen(config('sheba.s3_url')));
            (new FileRepository())->deleteFileFromCDN($filename);
        }
    }

    private function updateEmail($email) {
        $this->partner->email = $email;
        $this->partner->save();
    }

    /**
     * @param $image
     * @param $name
     * @return string
     */
    private function _saveDocumentImage($image, $name): string
    {
        return $this->saveFileToCDN($image, getComplianceFolder(), $name);
    }
}
