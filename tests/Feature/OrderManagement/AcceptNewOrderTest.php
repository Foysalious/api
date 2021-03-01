<?php namespace Tests\Feature\OrderManagement;


use App\Jobs\Job;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\CategoryPartner\CategoryPartner;
use Tests\Feature\FeatureTestCase;

class AcceptNewOrderTest extends FeatureTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->logIn();
        $this->mxOrderCreate();
        $this->categoryPartner = factory(CategoryPartner::class)->create([
            'partner_id'=>$this->partner->id,
            'category_id'=>$this->secondaryCategory->id
        ]);
        DB::table('category_resource')->truncate();
        DB::table('category_partner_resource')->truncate();
        $this->secondaryCategory->partnerResources()->attach($this->partner_resource);
        $this->secondaryCategory->resources()->attach($this->resource);


    }
    /* test cases for accept order post api */
    public function testOrderStatusIsAccepted(){
        dd($this->resource->remember_token);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token
        ]);
        $data = $response->decodeResponseJson();
        dd($data);
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }
}
