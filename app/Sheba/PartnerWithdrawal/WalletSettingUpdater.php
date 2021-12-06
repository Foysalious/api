<?php

namespace Sheba\PartnerWithdrawal;

use App\Models\Partner;
use App\Models\PartnerWalletSetting;
use Carbon\Carbon;
use Sheba\Dal\PartnerWalletSettingUpdateLog\PartnerWalletSettingUpdateLogRepositoryInterface;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class WalletSettingUpdater
{
    use ModificationFields;

    /** @var PartnerWalletSettingUpdateLogRepositoryInterface */
    private $logRepo;

    /** @var Partner */
    private $partner;
    /** @var PartnerWalletSetting */
    private $setting;
    /** @var array */
    private $data;

    public function __construct(PartnerWalletSettingUpdateLogRepositoryInterface $log_repo)
    {
        $this->logRepo = $log_repo;
    }

    public function setSetting(PartnerWalletSetting $setting)
    {
        $this->setting = $setting;
        return $this;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function update()
    {
        $this->prepareUpdateData();
        $log_data = $this->prepareLogData();
        $this->setting->update($this->withUpdateModificationField($this->data));
        $this->logRepo->create($log_data);
    }

    private function prepareUpdateData()
    {
        if (array_key_exists('security_money', $this->data)) {
            $this->data['security_money_received'] = true;
        }
        if (array_key_exists('min_wallet_threshold', $this->data)) {
            $this->prepareResetData();
        }
    }

    private function prepareResetData()
    {
        if ($this->isResetDateAdded()) {
            $this->data['old_credit_limit'] = $this->setting->min_wallet_threshold;
        } else {
            if ($this->isResetDateRemoved()) {
                $this->data['reset_credit_limit_after'] = null;
                $this->data['old_credit_limit'] = null;
            } else {
                $this->keepResetDateAsItIs();
            }
        }
    }

    private function isResetDateAdded()
    {
        return $this->data['reset_credit_limit_after'];
    }

    private function isResetDateRemoved()
    {
        return !$this->data['reset_credit_limit_after'] && $this->setting->reset_credit_limit_after;
    }

    private function keepResetDateAsItIs()
    {
        unset($this->data['reset_credit_limit_after']);
    }

    private function prepareLogData()
    {
        $log = isset($this->data['log']) ? $this->data['log'] : null;
        unset($this->data['log']);
        $fields = $this->getUpdatedFields($this->data);
        $old_values = $this->getCurrentValuesOfUpdatedFields($fields);
        $new_values = $this->data;
        if (array_key_exists('reset_credit_limit_after', $old_values) && $old_values['reset_credit_limit_after'] instanceof Carbon) {
            $old_values['reset_credit_limit_after'] = $old_values['reset_credit_limit_after']->toDateString();
        }
        if (array_key_exists('reset_credit_limit_after', $new_values) && $new_values['reset_credit_limit_after'] instanceof Carbon) {
            $new_values['reset_credit_limit_after'] = $new_values['reset_credit_limit_after']->toDateString();
        }
        $data = (new RequestIdentification())->set(
            [
                'partner_wallet_setting_id' => $this->setting->id,
                'fields' => json_encode($fields),
                'old_values' => json_encode($old_values),
                'new_values' => json_encode($new_values),
                'log' => $log
            ]
        );
        unset($data['created_by_type']);
        return $data;
    }

    private function getUpdatedFields($data)
    {
        return array_keys($data);
    }

    private function getCurrentValuesOfUpdatedFields($fields)
    {
        $values = [];
        foreach ($fields as $field) {
            $values[$field] = $this->setting->{$field};
        }
        return $values;
    }
}