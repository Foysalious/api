<?php namespace Tests\Feature\SmanagerRegistration;

use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\PartnerSubscriptionPackage;
use App\Models\Resource;
use Sheba\Sms\SmsVendor;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockSmsVendor;

class RegistrationTest extends FeatureTestCase
{
    /** @var PartnerSubscriptionPackage  */
    private $subscription;

    public function setUp()
    {
        parent::setUp();
        $this->truncateTables([
            PartnerSubscriptionPackage::class,
            Partner::class,
            Resource::class,
            PartnerResource::class,
        ]);

        $this->logIn();
        $this->subscription = factory(PartnerSubscriptionPackage::class)->create();

        $this->app->singleton(SmsVendor::class, MockSmsVendor::class);
    }

    public function testRegistrationApiReturnsSuccessfulResponseCode()
    {
        $response = $this->post("v2/profile/registration/partner", [
            "business_type" => "মোবাইল এবং গ্যাজেট",
            "company_name" => "erp tety",
            "gender" => "পুরুষ",
            "from" => "manager-app",
            "name" => "razoan",
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        dd($data);

    }

}