<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase {

    /** @test */
    function charges_with_a_valid_payment_token_are_successful() {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }

    /**
     * @test
     * @expectedException
     */
    function charges_with_an_invalid_payment_token_fail() {
        try {
            $paymentGateway = new FakePaymentGateway;
            $paymentGateway->charge(2500, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->addToAssertionCount(1);
            return;
        }

        $this->fail();
    }

    /** @test */
    function running_a_hook_before_the_first_charge() {
        
    }


}