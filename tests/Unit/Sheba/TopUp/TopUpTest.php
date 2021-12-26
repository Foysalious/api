<?php namespace Tests\Unit\Sheba\TopUp;

use App\Models\TopUpOrder;
use Sheba\TopUp\StatusChanger;
use Sheba\TopUp\TopUpRechargeManager;
use Sheba\TopUp\TopUpValidator;
use Sheba\TopUp\Vendor\Response\TopUpErrorResponse;
use Tests\Unit\UnitTestCase;

class TestableTopUpOrder extends TopUpOrder
{
    public function update(array $attributes = [], array $options = [])
    {
        return $this;
    }
}

class TopUpTest extends UnitTestCase
{
    /** @var TestableTopUpOrder */
    private $topUpOrder;

    public function setUp()
    {
        $this->topUpOrder = new TestableTopUpOrder();
        parent::setUp();
    }

    public function testShouldStateNotSuccessfulWithInvalidOrder()
    {
        $top_up = $this->tryToRechargeInvalidOrder();
        $this->assertTrue($top_up->isNotSuccessful());
    }

    public function testShouldStateErrorWithInvalidOrder()
    {
        $top_up = $this->tryToRechargeInvalidOrder();
        $this->shouldNotThrowException(function () use ($top_up) {
            $this->assertEquals($this->getError(), $top_up->getError());
        });
    }

    public function testInvalidOrderShouldUpdateAsFailedOrder()
    {
        $this->tryToRechargeInvalidOrder();
        $this->assertTrue($this->topUpOrder->isFailed());
        $this->assertEquals($this->getError()->toJson(), $this->topUpOrder->transaction_details);
    }

    /**
     * @return TopUpRechargeManager
     */
    private function tryToRechargeInvalidOrder()
    {
        $top_up = new TopUpRechargeManager($this->getValidationWithError(), app(StatusChanger::class));
        $top_up->setTopUpOrder($this->topUpOrder);
        $this->shouldNotThrowException(function () use ($top_up) {
            $top_up->recharge();
        });
        return $top_up;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject | TopUpValidator
     */
    private function getValidationWithError()
    {
        $validator = $this->getMock(TopUpValidator::class);
        $validator->method('hasError')->willReturn(true);
        $validator->method('setTopUpOrder')->willReturn($validator);
        $validator->method('validate')->willReturn($validator);
        $validator->method('getError')->willReturn($this->getError());
        return $validator;
    }

    private function getError()
    {
        $error = new TopUpErrorResponse();
        $error->errorCode = 400;
        $error->errorMessage = "Some Error.";
        return $error;
    }
}
