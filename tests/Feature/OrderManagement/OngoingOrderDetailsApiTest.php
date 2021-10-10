<?php namespace Tests\Feature\OrderManagement;


use App\Models\Job;
use Tests\Feature\FeatureTestCase;

class OngoingOrderDetailsApiTest extends FeatureTestCase
{
    function setUp()
    {
        parent::setUp();
        $this->logIn();
        $this->mxOrderCreate();
    }

    public function testOngoingOrderCode()
    {
        $this->job->update(['status'=>'Accepted']);
        $response=$this->get('v1/partners/'.$this->partner->id.'/orders/'.$this->order->id.'?remember_token='.$this->resource->remember_token.'&filter = ongoing');
        $data = $response->decodeResponseJson();
        $this->assertEquals("D-008001-0001",$data['order']['code']);
    }

    public function testOngoingOrderStatus()
    {
        $this->job->update(['status'=>'Accepted']);
        $response=$this->get('v1/partners/'.$this->partner->id.'/orders/'.$this->order->id.'?remember_token='.$this->resource->remember_token.'&filter = ongoing');
        $data = $response->decodeResponseJson();
        $this->assertEquals("Accepted",$data['order']['order_status']);
    }

    public function testOngoingOrderResourceId()
    {
        $this->job->update(['status'=>'Accepted']);
        $response=$this->get('v1/partners/'.$this->partner->id.'/orders/'.$this->order->id.'?remember_token='.$this->resource->remember_token.'&filter = ongoing');
        $data = $response->decodeResponseJson();
        $this->assertEquals($this->job->resource_id,$data['order']['jobs'][0]['resource_id']);
    }

    public function testOngoingOrderServiceName()
    {
        $this->job->update(['status'=>'Accepted']);
        $response=$this->get('v1/partners/'.$this->partner->id.'/orders/'.$this->order->id.'?remember_token='.$this->resource->remember_token.'&filter = ongoing');
        $data = $response->decodeResponseJson();
        $this->assertEquals($this->job->job_name,$data['order']['jobs'][0]['service_name']);
    }

    public function testOngoingOrderLocation()
    {
        $this->job->update(['status'=>'Accepted']);
        $response=$this->get('v1/partners/'.$this->partner->id.'/orders/'.$this->order->id.'?remember_token='.$this->resource->remember_token.'&filter = ongoing');
        $data = $response->decodeResponseJson();
        $this->assertEquals('Mohammadpur',$data['order']['location']);
    }

    public function testOngoingOrderPreferredTime()
    {
        $this->job->update(['status'=>'Accepted']);
        $response=$this->get('v1/partners/'.$this->partner->id.'/orders/'.$this->order->id.'?remember_token='.$this->resource->remember_token.'&filter = ongoing');
        $data = $response->decodeResponseJson();
        $this->assertEquals('7:48 PM-8:48 PM',$data['order']['jobs'][0]['preferred_time']);
    }

    public function testOngoingOrderScheduleDate()
    {
        $this->job->update(['status'=>'Accepted']);
        $response=$this->get('v1/partners/'.$this->partner->id.'/orders/'.$this->order->id.'?remember_token='.$this->resource->remember_token.'&filter = ongoing');
        $data = $response->decodeResponseJson();
        $this->assertEquals($this->job->schedule_date,$data['order']['jobs'][0]['schedule_date']);
    }

    public function testOngoingOrderDeliveryAddress()
    {
        $this->job->update(['status'=>'Accepted']);
        $response=$this->get('v1/partners/'.$this->partner->id.'/orders/'.$this->order->id.'?remember_token='.$this->resource->remember_token.'&filter = ongoing');
        $data = $response->decodeResponseJson();
       // dd($data);
        $this->assertEquals($this->order->delivery_address,$data['order']['address']);
    }

    public function testOngoingOrderServiceQuantity()
    {
        $this->job->update(['status'=>'Accepted']);
        $response=$this->get('v1/partners/'.$this->partner->id.'/orders/'.$this->order->id.'?remember_token='.$this->resource->remember_token.'&filter = ongoing');
        $data = $response->decodeResponseJson();
        $this->assertEquals($this->job->service_quantity,$data['order']['jobs'][0]['service_quantity']);
    }
}
