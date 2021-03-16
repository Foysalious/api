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
        $this->artisan('migrate');
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
            /*'subscription_rules' =>'{"resource_cap":{"value":5,"is_published":1},"commission":{"value":20,"is_published":1},"fee":{"monthly":{"value":95,"is_published":1},"yearly":{"value":310,"is_published":1},"half_yearly":{"value":410,"is_published":0}},"access_rules":{"loan":true,"dashboard_analytics":true,"pos":{"invoice":{"print":true,"download":true},"due":{"alert":true,"ledger":true},"inventory":{"warranty":{"add":true}},"report":false,"ecom":{"product_publish":false,"webstore_publish":true}},"extra_earning":{"topup":true,"movie":true,"transport":true,"utility":true},"resource":{"type":{"add":true}},"expense":true,"extra_earning_global":true,"customer_list":true,"marketing_promo":true,"digital_collection":true,"old_dashboard":false,"notification":true,"eshop":true,"emi":true,"due_tracker":true},"tags":{"monthly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"half_yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"\u09ae\u09be\u09b8\u09bf\u0995","price":95,"duration":30,"is_published":0},{"title":"yearly","title_bn":"\u09ac\u09be\u09ce\u09b8\u09b0\u09bf\u0995","price":310,"duration":365,"is_published":0},{"title":"two_yearly","title_bn":"\u09a6\u09cd\u09ac\u09bf-\u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":735,"duration":730,"is_published":1},{"title":"3_monthly","title_bn":"\u09e9 \u09ae\u09be\u09b8","price":285,"duration":90,"is_published":0},{"title":"6_monthly","title_bn":"\u09ec \u09ae\u09be\u09b8","price":570,"duration":180,"is_published":0},{"title":"9_monthly","title_bn":"\u09ef \u09ae\u09be\u09b8","price":855,"duration":270,"is_published":0},{"title":"11_month","title_bn":"egaro mash","price":880,"duration":330,"is_published":1},{"title":"13_month","title_bn":"month","price":900,"duration":800,"is_published":1}]}',
            'billing_type' => "monthly"*/
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
