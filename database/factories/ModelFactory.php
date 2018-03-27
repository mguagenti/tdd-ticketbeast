<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

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

$factory->state(App\Concert::class, 'published', function (Faker $faker) {
    return [
        'published_at' => Carbon::parse('-1 week')
    ];
});

$factory->state(App\Concert::class, 'unpublished', function (Faker $faker) {
    return [
        'published_at' => null
    ];
});