<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

/**
 * Create a concert with a date two weeks in the future.
 */
$factory->define(App\Concert::class, function (Faker $faker) {
    return [
        'title'                     => 'Example Band',
        'subtitle'                  => 'With The Fake Openers',
        'date'                      => Carbon::parse('+2 weeks'),
        'ticket_price'              => 2000,
        'venue'                     => 'The Example Theatre',
        'address'                   => '123 Example Lane',
        'city'                      => 'Fakeville',
        'state'                     => 'ON',
        'zip'                       => '90210',
        'additional_information'    => 'Some sample information.'
    ];
});

/**
 * Create a concert that was published a week ago from this day.
 */
$factory->state(App\Concert::class, 'published', function (Faker $faker) {
    return [
        'published_at' => Carbon::parse('-1 week')
    ];
});

/**
 * Create a concert with an unpublished status.
 */
$factory->state(App\Concert::class, 'unpublished', function (Faker $faker) {
    return [
        'published_at' => null
    ];
});