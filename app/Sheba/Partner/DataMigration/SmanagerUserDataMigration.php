<?php namespace Sheba\Partner\DataMigration;

use App\Models\Partner;
use App\Sheba\SmanagerUserService\SmanagerUserServerClient;
use Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToSmanagerUserJob;
use Sheba\Repositories\PartnerRepository;
use DB;

class SmanagerUserDataMigration
{
    const CHUNK_SIZE = 10;
    /** @var Partner */
    private $partner;
    /** @var SmanagerUserServerClient */
    private $client;
    /** @var PartnerRepository */
    private $partnerRepository;

    /**
     * PosCustomerDataMigration constructor.
     * @param SmanagerUserServerClient $client
     * @param PartnerRepository $partnerRepository
     */
    public function __construct(SmanagerUserServerClient $client, PartnerRepository $partnerRepository)
    {
        $this->client = $client;
        $this->partnerRepository = $partnerRepository;
    }

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
        $this->migratePartner();
        $this->migratePosCustomers();
    }

    private function migratePartner()
    {
        dispatch(new PartnerDataMigrationToSmanagerUserJob($this->partner, ['partner_info' => $this->generatePartnerMigrationData()]));
    }

    private function generatePartnerMigrationData()
    {
        return [
            'id' => $this->partner->id,
            'name' => $this->partner->name,
            'sub_domain' => $this->partner->sub_domain,
        ];
    }

    private function migratePosCustomers()
    {
        $chunks = array_chunk($this->generatePosCustomersMigrationData(), self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            dispatch(new PartnerDataMigrationToSmanagerUserJob($this->partner, ['pos_customers' => $chunk]));
        }
    }

    private function generatePosCustomersMigrationData()
    {
        return DB::table('partner_pos_customers')
            ->where('partner_id', $this->partner->id)
            ->join('pos_customers', 'partner_pos_customers.customer_id', '=', 'pos_customers.id')
            ->join('profiles', 'pos_customers.profile_id', '=', 'profiles.id')
            ->select('partner_pos_customers.customer_id', 'partner_pos_customers.partner_id', 'partner_pos_customers.nick_name',
                'partner_pos_customers.is_supplier', 'profiles.name', 'profiles.bn_name', 'profiles.mobile', 'profiles.email',
                'profiles.password', 'profiles.is_blacklisted', 'profiles.login_blocked_until', 'profiles.fb_id', 'profiles.google_id',
                'profiles.mobile_verified', 'profiles.email_verified', 'profiles.email_verified_at', 'profiles.address', 'profiles.gender',
                'profiles.dob', 'profiles.pro_pic', 'profiles.created_by_name', 'profiles.updated_by_name', 'profiles.created_at',
                'profiles.updated_at')
            ->get();
    }
}