<?php

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewConcertListingTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    function user_can_view_a_published_concert_listing() {
        // Arrange
        // Create Concert
        $concert = factory(Concert::class)->states('published')->create([
            'title'                     => 'The Red Chord',
            'subtitle'                  => 'With Animosity and Lethargy',
            'date'                      => Carbon::parse('December 13, 2016 8:00pm'),
            'ticket_price'              => 3250,
            'venue'                     => 'The Mosh Pit',
            'address'                   => '123 Example Lane',
            'city'                      => 'Laraville',
            'state'                     => 'ON',
            'zip'                       => '17916',
            'additional_information'    => 'Please call (555) 555-5555',
        ]);

        // View Listing
        $this->visit('/concert/' . $concert->id)
            ->see('The Red Chord')
            ->see('With Animosity and Lethargy')
            ->see('December 13, 2016')
            ->see('8:00pm')
            ->see('32.50')
            ->see('The Mosh Pit')
            ->see('123 Example Lane')
            ->see('Laraville, ON 17916')
            ->see('Please call (555) 555-5555');
    }

    /** @test */
    function user_cannot_view_unpublished_concert_listings() {
        $concert = factory(Concert::class)->states('unpublished')->create();

        $this->get('/concert/' . $concert->id)
            ->assertResponseStatus(404);
    }


}
