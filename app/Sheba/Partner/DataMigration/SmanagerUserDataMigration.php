<?php namespace Sheba\Partner\DataMigration;

use App\Models\Partner;
use Illuminate\Support\Facades\Redis;
use Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToSmanagerUserJob;
use DB;

class SmanagerUserDataMigration
{
    const CHUNK_SIZE = 50;
    private $currentQueue = 1;
    /** @var Partner */
    private $partner;
    private $partnerInfo;
    private $posCustomers;
    private $queue_and_connection_name;
    private $shouldQueue;

    /**
     * @param Partner $partner
     * @return SmanagerUserDataMigration
     */
    public function setPartner(Partner $partner): SmanagerUserDataMigration
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $queue_and_connection_name
     * @return SmanagerUserDataMigration
     */
    public function setQueueAndConnectionName($queue_and_connection_name)
    {
        $this->queue_and_connection_name = $queue_and_connection_name;
        return $this;
    }

    /**
     * @param mixed $shouldQueue
     * @return SmanagerUserDataMigration
     */
    public function setShouldQueue($shouldQueue)
    {
        $this->shouldQueue = $shouldQueue;
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
        $this->shouldQueue ? dispatch(new PartnerDataMigrationToSmanagerUserJob($this->partner, ['partner_info' => $data], $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue)) :
            dispatchJobNow(new PartnerDataMigrationToSmanagerUserJob($this->partner, ['partner_info' => $data], $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue));
        $this->increaseCurrentQueueValue();
    }

    private function generatePartnerMigrationData()
    {
        return [
            'originalId' => $this->partner->id,
            'name' => $this->partner->name,
            'sub_domain' => $this->partner->sub_domain,
        ];
    }

    private function migratePosCustomers($data)
    {
        if(!is_array($data)){
            $data = $data->toArray();
        }
        $chunks = array_chunk($data, self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            $this->setRedisKey();
            $this->shouldQueue ? dispatch(new PartnerDataMigrationToSmanagerUserJob($this->partner, ['pos_customers' => $chunk], $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue)) :
                dispatchJobNow(new PartnerDataMigrationToSmanagerUserJob($this->partner, ['pos_customers' => $chunk], $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue));
            $this->increaseCurrentQueueValue();
        }
    }

    private function generatePosCustomersMigrationData()
    {
        $query = DB::raw("(CASE WHEN partner_pos_customers.nick_name IS NOT NULL  THEN partner_pos_customers.nick_name  ELSE profiles.name END) as name");
        return DB::table('partner_pos_customers')
            ->where('partner_id', $this->partner->id)
            ->where(function ($q) {
                $q->where('is_migrated', null)->orWhere('is_migrated', 0);
            })
            ->join('pos_customers', 'partner_pos_customers.customer_id', '=', 'pos_customers.id')
            ->join('profiles', 'pos_customers.profile_id', '=', 'profiles.id')
            ->select('partner_pos_customers.id', 'partner_pos_customers.customer_id as previous_id', 'partner_pos_customers.partner_id', $query,
                'partner_pos_customers.is_supplier', 'partner_pos_customers.note', 'profiles.mobile', 'profiles.email', 'profiles.fb_id', 'profiles.google_id',
                'profiles.mobile_verified', 'profiles.email_verified', 'profiles.email_verified_at', 'profiles.address', 'profiles.gender',
                'profiles.dob', 'profiles.pro_pic', 'profiles.created_by_name', 'profiles.updated_by_name', 'profiles.created_at',
                'profiles.updated_at')
            ->get();
    }

    private function setRedisKey()
    {
        $count = (int)Redis::get('PosOrderDataMigrationCount::' . $this->queue_and_connection_name);
        $count ? $count++ : $count = 1;
        Redis::set('PosOrderDataMigrationCount::' . $this->queue_and_connection_name, $count);
        Redis::set('DataMigration::Partner::' . $this->partner->id . '::SmanagerUser::Queue::' . $this->currentQueue, 'initiated');
    }

    private function increaseCurrentQueueValue()
    {
        $this->currentQueue += 1;
    }
}