<?php namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\Customer;
use App\Models\Member;
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
        /*\Illuminate\Support\Facades\DB::unprepared(file_get_contents('database/seeds/sheba_testing.sql'));
        $this->artisan('migrate');
        $this->beforeApplicationDestroyed(function () {
            \Illuminate\Support\Facades\DB::unprepared(file_get_contents('database/seeds/sheba_testing.sql'));
        });*/
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
            Profile::class,
            Affiliate::class,
            Customer::class,
            Member::class,
            Resource::class
        ]);

        $this->profile = factory(Profile::class)->create();
        $this->affiliate = factory(Affiliate::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->customer = factory(Customer::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->resource = factory(Resource::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->member = factory(Member::class)->create([
            'profile_id' => $this->profile->id
        ]);
    }

    protected function generateToken()
    {
        return JWTAuth::fromUser($this->profile, [
            'name' => $this->profile->name,
            'image' => $this->profile->pro_pic,
            'profile' => [
                'id' => $this->profile->id,
                'name' => $this->profile->name,
                'email_verified' => $this->profile->email_verified
            ],
            'customer' =>[
                'id' => $this->customer->id
            ],
            'resource' => [
                'id' => $this->resource->id
            ],
            'member' => [
                'id' => $this->member->id
            ],
            'business_member' => null,
            'affiliate' => [
                'id' => $this->affiliate->id
            ],
            'logistic_user' => null,
            'bank_user' => null,
            'strategic_partner_member' => null,
            'avatar' => null,
            "exp" => Carbon::now()->addDay()->timestamp
        ]);
    }

    private function createAuthTables()
    {
        $authorization_request = factory(AuthorizationRequest::class)->create([
            'profile_id' => $this->profile->id
        ]);
        factory(AuthorizationToken::class)->create([
            'authorization_request_id' => $authorization_request->id,
            'token' => $this->token
        ]);
    }

    protected function truncateTable($table)
    {
        $this->truncateTables([
            $table
        ]);
    }

    protected function truncateTables(array $tables)
    {
        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            $table::truncate();
        }
        Schema::enableForeignKeyConstraints();
    }
}
