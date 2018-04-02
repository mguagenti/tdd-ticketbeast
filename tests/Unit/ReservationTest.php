<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Reservation;

class ReservationTest extends TestCase {

    /** @test */
    function calculating_the_total_cost() {
        $tickets = Collect([
            (Object) ['price' => 1200],
            (Object) ['price' => 1200],
            (Object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
    }

}