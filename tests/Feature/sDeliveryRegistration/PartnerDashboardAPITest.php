<?php

namespace Tests\Feature\sDeliveryRegistration;

use App\Models\Partner;
use App\Models\PartnerResource;
use Tests\Feature\FeatureTestCase;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInfo;

class PartnerDashboardAPITest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->logIn();
        $this->truncateTables([
            Partner::class,
            PartnerResource::class,
            PartnerDeliveryInfo::class,
        ]);

        $this->partner = Partner::factory()->create(['sub_domain' => 'test786788.com']);

        $this->partner_resource = PartnerResource::factory()->create([
            'partner_id'    => $this->partner->id,
            'resource_id'   => $this->resource->id,
            'resource_type' => 'Admin',
        ]);

        PartnerDeliveryInfo::factory()->create(['partner_id' => $this->partner->id]);
    }
}
