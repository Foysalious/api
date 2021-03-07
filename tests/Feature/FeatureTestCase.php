<?php namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Customer;
use App\Models\Member;
use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\PartnerSubscriptionPackage;
use App\Models\Profile;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Sheba\Dal\AuthorizationRequest\AuthorizationRequest;
use Sheba\Dal\AuthorizationToken\AuthorizationToken;
use TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class FeatureTestCase extends TestCase
{
    use DatabaseMigrations;

    protected $token;
    /** @var Profile */
    protected $profile;
    /** @var Affiliate */
    protected $affiliate;
    /** @var Customer */
    protected $customer;
    /** @var Resource */
    protected $resource;
    /** @var Member */
    protected $member;
    /** @var Partner */
    protected $partner;
    /** @var PartnerResource */
    protected $partner_resource;
    // @var ParnerSubscriptionPackage
    protected $partner_package;
    /**
     * @var $business
     */
    protected $business;
    /**
     * @var $business_member
     */
    private $business_member;

    public function setUp()
    {
        parent::setUp();
    }

    public function get($uri, array $headers = [])
    {
        $uri = trim($this->baseUrl, '/') . '/' . trim($uri, '/');
        return parent::get($uri, $headers);
    }

    public function post($uri, array $data = [], array $headers = [])
    {
        $uri = trim($this->baseUrl, '/') . '/' . trim($uri, '/');
        return parent::post($uri, $data, $headers);
    }

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {
        /**
         * NO NEED TO RUN
         *
         * \Illuminate\Support\Facades\DB::unprepared(file_get_contents('database/seeds/sheba_testing.sql'));
         * $this->artisan('migrate');
         * $this->beforeApplicationDestroyed(function () {
         * \Illuminate\Support\Facades\DB::unprepared(file_get_contents('database/seeds/sheba_testing.sql'));
         * });*/
    }

    protected function logIn()
    {

        $this->createAccounts();
        $this->token = $this->generateToken();
        $this->createAuthTables();
    }

    private function createAccounts()
    {
        $this->truncateTables([
            Profile::class, Affiliate::class, Customer::class, Member::class, Resource::class, Partner::class, Business::class, BusinessMember::class
        ]);


        $this->profile = factory(Profile::class)->create();



        $this->createClientAccounts();
    }

    protected function truncateTables(array $tables)
    {
        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            $table::truncate();
        }
        Schema::enableForeignKeyConstraints();
    }

    private function createClientAccounts()
    {

        $this->affiliate = factory(Affiliate::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->customer = factory(Customer::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->resource = factory(Resource::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->partner_package = factory(PartnerSubscriptionPackage::class)->create();
        $this->partner = factory(Partner::class)->create([
            'package_id' => $this->partner_package->id
        ]);
        $this->partner_resource = factory(PartnerResource::class)->create([
            'resource_id' => $this->resource->id, 'partner_id' => $this->partner->id
        ]);
        $this->member = factory(Member::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->business = factory(Business::class)->create();
        $this->business_member = factory(BusinessMember::class)->create([
            'business_id' => $this->business->id, 'member_id' => $this->member->id
        ]);
    }

    protected function generateToken()
    {
        return JWTAuth::fromUser($this->profile, [
            'name' => $this->profile->name, 'image' => $this->profile->pro_pic, 'profile' => [
                'id' => $this->profile->id, 'name' => $this->profile->name, 'email_verified' => $this->profile->email_verified
            ], 'customer' => [
                'id' => $this->customer->id

            ],
            'resource' => [
                'id'=>$this->resource->id
            ],
            'member' => [
                'id' => $this->member->id
            ], 'business_member' => [
                'id' => $this->business_member->id, 'business_id' => $this->business->id, 'member_id' => $this->member->id, 'is_super' => 1
            ], 'affiliate' => [
                'id' => $this->affiliate->id
            ], 'logistic_user' => null, 'bank_user' => null, 'strategic_partner_member' => null, 'avatar' => null, "exp" => Carbon::now()->addDay()->timestamp
        ]);
    }

    private function createAuthTables()
    {
        $authorization_request = factory(AuthorizationRequest::class)->create([
            'profile_id' => $this->profile->id
        ]);
        factory(AuthorizationToken::class)->create([
            'authorization_request_id' => $authorization_request->id, 'token' => $this->token
        ]);
    }

    protected function logInWithMobileNEmail($mobile, $email = null)
    {
        $this->createAccountWithMobileNEmail($mobile, $email);
        $this->token = $this->generateToken();
        $this->createAuthTables();

    }

    private function createAccountWithMobileNEmail($mobile, $email = null)
    {
        $this->profile = factory(Profile::class)->create([

            'mobile' => $mobile, 'email' => $email,

        ]);


        $this->createClientAccounts();
    }

    protected function truncateTable($table)
    {
        $this->truncateTables([
            $table
        ]);
    }
}
