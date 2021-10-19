<?php namespace App\Sheba\UserMigration;

use App\Exceptions\Pos\DataMigrationException;
use App\Models\Partner;
use Sheba\Dal\UserMigration\UserStatus;
use Sheba\Partner\DataMigration\DataMigration;
use Exception;

class PosUserMigration extends UserMigrationRepository
{
    public function getBanner()
    {
        return 'pos-banner';
    }

    public function getStatusWiseResponse(): array
    {
        // TODO: Implement getStatusWiseResponse() method.
    }

    /**
     * @throws DataMigrationException
     * @throws Exception
     */
    public function updateStatus($status)
    {
        $accounting_status = $this->setModuleName(Modules::EXPENSE)->getStatus();
        if ($accounting_status != UserStatus::UPGRADED) throw new Exception('Please Complete Accounting Migration First!');
        if ($status == UserStatus::UPGRADING) {
            $current_status = $this->setModuleName(Modules::POS)->getStatus();
            if ($current_status == self::NOT_ELIGIBLE) throw new Exception('Sorry! Not Found');
            if ($current_status == UserStatus::UPGRADED) throw new Exception('Sorry! Already Migrated.');
            if ($current_status == UserStatus::UPGRADING ) throw new Exception('Sorry! Already Migrating.');
            /** @var DataMigration $dataMigration */
            $dataMigration = app(DataMigration::class);
            $partner = Partner::find($this->userId);
            $dataMigration->setPartner($partner)->migrate();
        }
        return $this->updateMigrationStatus($status);
    }
}