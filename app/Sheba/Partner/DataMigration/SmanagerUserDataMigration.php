<?php namespace Sheba\Partner\DataMigration;

use App\Models\Partner;
use Illuminate\Support\Facades\Redis;
use Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToSmanagerUserJob;
use DB;

class SmanagerUserDataMigration
{
    const CHUNK_SIZE = 10;
    private $currentQueue = 1;
    /** @var Partner */
    private $partner;
    private $partnerInfo;
    private $posCustomers;

    /**
     * @param Partner $partner
     * @return SmanagerUserDataMigration
     */
    public function setPartner(Partner $partner): SmanagerUserDataMigration
    {
        $this->partner = $partner;
        return $this;
    }

    public function migrate()
    {
        $this->generateMigrationData();
        $this->migratePartner($this->partnerInfo);
        $this->migratePosCustomers($this->posCustomers);
    }

    private function generateMigrationData()
    {
        $this->partnerInfo = $this->generatePartnerMigrationData();
        $this->posCustomers = $this->generatePosCustomersMigrationData();
    }

    private function migratePartner($data)
    {
        $this->setRedisKey();
        dispatch(new PartnerDataMigrationToSmanagerUserJob($this->partner, ['partner_info' => $data], $this->currentQueue));
        $this->increaseCurrentQueueValue();
    }

    private function generatePartnerMigrationData()
    {
        return [
            'previous_id' => $this->partner->id,
            'name' => $this->partner->name,
            'sub_domain' => $this->partner->sub_domain,
        ];
    }

    private function migratePosCustomers($data)
    {
        $chunks = array_chunk($data, self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            $this->setRedisKey();
            dispatch(new PartnerDataMigrationToSmanagerUserJob($this->partner, ['pos_customers' => $chunk], $this->currentQueue));
            $this->increaseCurrentQueueValue();
        }
    }

    private function generatePosCustomersMigrationData()
    {
        return DB::table('partner_pos_customers')
            ->where('partner_id', $this->partner->id)
            ->join('pos_customers', 'partner_pos_customers.customer_id', '=', 'pos_customers.id')
            ->join('profiles', 'pos_customers.profile_id', '=', 'profiles.id')
            ->select('partner_pos_customers.customer_id as previous_id', 'partner_pos_customers.partner_id', 'partner_pos_customers.nick_name',
                'partner_pos_customers.is_supplier', 'profiles.name', 'profiles.bn_name', 'profiles.mobile', 'profiles.email',
                'profiles.password', 'profiles.is_blacklisted', 'profiles.login_blocked_until', 'profiles.fb_id', 'profiles.google_id',
                'profiles.mobile_verified', 'profiles.email_verified', 'profiles.email_verified_at', 'profiles.address', 'profiles.gender',
                'profiles.dob', 'profiles.pro_pic', 'profiles.created_by_name', 'profiles.updated_by_name', 'profiles.created_at',
                'profiles.updated_at')
            ->get();
    }

    private function setRedisKey()
    {
        Redis::set('DataMigration::Partner::' . $this->partner->id . '::SmanagerUser::Queue::' . $this->currentQueue, 'initiated');
    }

    private function increaseCurrentQueueValue()
    {
        $this->currentQueue += 1;
    }
}