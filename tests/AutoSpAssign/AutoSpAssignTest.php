<?php namespace AutoSpAssign;


use App\Models\Customer;
use Sheba\AutoSpAssign\Initiator;
use TestCase;

class AutoSpAssignTest extends TestCase
{

    public function test()
    {
        $init=new Initiator();
        $init->setCustomer(Customer::find(11))->setPartnerIds([595,975])->assign();
    }
}