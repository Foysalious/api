<?php

namespace Sheba\ComplianceInfo;

use App\Models\Partner;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Repositories\PartnerTransactionRepository;

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
     * @param int $additional_fields
     * @return array
     */
    public function get($additional_fields = 0): array
    {
        return $this->formatData($additional_fields);
    }

    private function formatData($additional_fields): array
    {
        $data = [
            "shop_type" => $this->partner->basicInformations->shop_type ?? '',
            "monthly_transaction_volume" => $this->partner->basicInformations->monthly_transaction_volume ?? '',
            "registration_year" => $this->partner->basicInformations->registration_year ? Carbon::parse($this->partner->basicInformations->registration_year)->toDateString() : '',
            "email" => $this->partner->email ?? '',
            "bank_account" => $this->formatBankAccount($this->partner->withdrawalBankInformations()->first()),
            "trade_license" => $this->partner->basicInformations->trade_license ?? "",
            "tin_no"        => $this->partner->basicInformations->tin_no ?? "",
            "tin_licence_photo" => $this->partner->basicInformations->tin_licence_photo ?? "",
            "electricity_bill_image" => $this->partner->basicInformations->electricity_bill_image ?? "",
            "website"       => $this->partner->basicInformations->website_url ?? "",
        ];

        return $additional_fields ? array_merge($data, [
            "cpv_status"         => $this->partner->basicInformations->cpv_status,
            "grantor"            => $this->partner->basicInformations->grantor,
            "security_cheque"    => $this->partner->basicInformations->security_cheque
        ]) : $data;
    }

    /**
     * @param $account
     * @return array
     */
    private function formatBankAccount($account): array
    {
        if(empty($account)) return [];
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
        $this->updateEmail($data['email']);
        $data = array_except($data, 'email');

        if(isset($data['tin_licence_photo'])) $this->getImageUrl('tin_licence_photo', $data);
        else $data['tin_licence_photo'] = $this->partner->basicInformations->tin_licence_photo;
        if(isset($data['electricity_bill_image'])) $this->getImageUrl('electricity_bill_image',$data);
        else $data['electricity_bill_image'] = $this->partner->basicInformations->electricity_bill_image;

        if(empty($data['registration_year'])) $data = array_except($data, 'registration_year');
        $this->partner->basicInformations->update($this->withUpdateModificationField($data));
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

    /**
     * @return string
     */
    public function getComplianceStatus(): string
    {
        $total = (new PartnerTransactionRepository($this->partner))->thisMonthTotalPaymentLinkCredit();

        if ($total >= config('compliance_info.third_transaction_limit')) return $this->getStatusByCondition('third_limit_required_fields', 1);

        elseif ($total >= config('compliance_info.second_transaction_limit')) return $this->getStatusByCondition('second_limit_required_fields', 1);

        elseif ($total >= config('compliance_info.first_transaction_limit')) return $this->getStatusByCondition('first_limit_required_fields', 0);

        return Statics::VERIFIED;
    }

    private function getStatusByCondition($required_fields_key, $additional_fields): string
    {
        $data = $this->get($additional_fields);
        $required_fields = config("compliance_info.$required_fields_key");
        if($this->allValuesExist($data, $required_fields)) return Statics::VERIFIED;
        return Statics::REJECTED;
    }

    /**
     * @param $data
     * @param $fields
     * @return bool
     */
    private function allValuesExist($data, $fields): bool
    {
        foreach ($fields as $field) {
            if(empty($data[$field])) return false;
            elseif($field === "cpv_status" && $data[$field] !== "verified") return false;
        }
        return true;
    }
}
