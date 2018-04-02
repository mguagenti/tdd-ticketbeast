<?php

namespace Tests\Feature;

use App\Concert;
use Tests\TestCase;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PurchaseTicketsTest extends TestCase {

    use DatabaseMigrations;

    /**
     * Set up the test and create an instance of the fake payment gateway.
     */
    protected function setUp() {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /**
     * Order tickets for the concert with the specified values.
     *
     * @param Concert $concert    The concert that we will be ordering tickets for.
     * @param array   $params     Array of parameters for the concert.
     */
    private function orderTickets($concert, $params) {
        $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    /**
     * Checks if message failed validation and returned the proper response.
     *
     * @param string $field     The key to check within the errors array.
     */
    private function assertValidationError($field) {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, $this->decodeResponseJson()['errors']);
    }

    /** @test */
    function customer_can_purchase_tickets_to_a_published_concert() {
        $concert = factory(Concert::class)->states('published')->create([
           'ticket_price' => 3250
        ])->addTickets(3);

        $this->orderTickets($concert, [
            'email'             => 'john@example.com',
            'ticket_quantity'   => 3,
            'payment_token'     => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertResponseStatus(201);

        $this->seeJsonSubset([
            'email'             => 'john@example.com',
            'ticket_quantity'   => 3,
            'amount'            => 9750
        ]);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ordersFor('john@example.com')->first()->ticketQuantity());
    }

    /** @test */
    function cannot_purchase_tickets_to_unpublished_concert() {
        $concert = factory(Concert::class)->states('unpublished')->create()
                    ->addTickets(3);

        $this->orderTickets($concert, [
            'email'             => 'john@example.com',
            'ticket_quantity'   => 3,
            'payment_token'     => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertResponseStatus(404);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /** @test */
    function an_order_is_not_created_if_payment_fails() {
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 3250
        ])->addTickets(3);

        $order = $this->orderTickets($concert, [
            'email'             => 'john@example.com',
            'ticket_quantity'   => 3,
            'payment_token'     => 'invalid'
        ]);

        $this->assertResponseStatus(422);
        $this->assertNull($order);
    }

    /** @test */
    function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase() {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(3);

        $this->orderTickets($concert, [
            'email'             => 'personA@example.com',
            'ticket_quantity'   => 51,
            'payment_token'     => $this->paymentGateway->getValidTestToken()
        ]);

    }

    /** @test */
    function email_is_required_to_purchase_tickets() {
        $concert = factory(Concert::class)->create();

        $this->orderTickets($concert, [
            'ticket_quantity'   => 3,
            'payment_token'     => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError('email');
    }

    /** @test */
    function valid_email_is_required_to_purchase_tickets() {
        $concert = factory(Concert::class)->create();

        $this->orderTickets($concert, [
            'email'             => 'abc-abc-abc',
            'ticket_quantity'   => 3,
            'payment_token'     => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError('email');
    }

    /** @test */
    function ticket_quantity_is_required_to_purchase_tickets() {
        $concert = factory(Concert::class)->create();

        $this->orderTickets($concert, [
            'email'             => 'jane@example.com',
            'payment_token'     => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    function ticket_quantity_must_be_at_least_1_to_purchase_tickets() {
        $concert = factory(Concert::class)->create();

        $this->orderTickets($concert, [
            'email'             => 'abc-abc-abc',
            'ticket_quantity'   => 0,
            'payment_token'     => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    function payment_token_is_required() {
        $concert = factory(Concert::class)->create();

        $this->orderTickets($concert, [
            'email'             => 'jane@example.com',
            'ticket_quantity'   => 3
        ]);

        $this->assertValidationError('payment_token');
    }

    /** @test */
    function customer_cannot_purchase_more_tickets_than_remain() {
        $concert = factory(Concert::class)->states('published')->create()
                    ->addTickets(50);

        $this->orderTickets($concert, [
            'email'             => 'jane@example.com',
            'ticket_quantity'   => 51,
            'payment_token'     => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertResponseStatus(422);

        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertNull($concert->ordersFor('john@example.com')->first());

        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }


}
