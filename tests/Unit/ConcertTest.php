<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Carbon\Carbon;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;

class ConcertTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    function can_get_formatted_date() {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */
    function can_get_formatted_start_time() {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:00:00'),
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /** @test */
    function can_get_ticket_price_in_dollars() {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750,
        ]);

        $this->assertEquals('67.50', $concert->ticket_price_in_dollars);
    }

    /** @test */
    function concerts_with_a_published_at_date_are_published() {
        $publishedConcertA = factory(Concert::class)->create([
            'published_at'  => Carbon::parse('-1 week')
        ]);

        $publishedConcertB = factory(Concert::class)->create([
            'published_at'  => Carbon::parse('-1 week')
        ]);

        $unpublishedConcert = factory(Concert::class)->create([
            'published_at'  => null
        ]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /** @test */
    function can_order_concert_tickets() {
        $concert = factory(Concert::class)->create()
                    ->addTickets(3);

        $order = $concert->orderTickets('jane@example.com', 3);

        $this->assertEquals('jane@example.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    function can_add_tickets() {
        $concert = factory(Concert::class)->create()
                    ->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    function tickets_remaining_does_not_include_tickets_associated_with_an_order() {
        $concert = factory(Concert::class)->create()
                    ->addTickets(50);

        $order = $concert->orderTickets('jane@example.com', 30);

        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    function trying_to_purchase_more_tickets_than_remain_throws_an_exception() {
        $concert = factory(Concert::class)->create()
                    ->addTickets(10);

        try {
            $concert->orderTickets('jane@example.com', 11);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('jane@example.com'));
            $this->assertNull($concert->ordersFor('john@example.com')->first());
            $this->assertEquals(10, $concert->ticketsRemaining());

            return;
        }

        $this->fail("Order succeeded even though there are not enough tickets remaining.");
    }

    /** @test */
    function cannot_order_tickets_that_have_already_been_purchased() {
        $concert = factory(Concert::class)->create()
                    ->addTickets(10);

        $concert->orderTickets('jane@example.com', 8);

        try {
            $concert->orderTickets('john@example.com', 3);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('john@example.com'));
            $this->assertNull($concert->ordersFor('john@example.com')->first());
            $this->assertEquals(2, $concert->ticketsRemaining());

            return;
        }

        $this->fail("Order succeeded even though there are not enough tickets remaining.");

    }


}
