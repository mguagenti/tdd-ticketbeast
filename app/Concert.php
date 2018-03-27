<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concert extends Model {

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $dates = ['date'];

    /**
     * Get the published concerts.
     *
     * @param  $query
     * @return mixed
     */
    public function scopePublished($query) {
        return $query->whereNotNull('published_at');
    }

    /**
     * Get the date formatted as a human-readable string.
     *
     * @return string
     */
    public function getFormattedDateAttribute() {
        return $this->date->format('F j, Y');
    }

    /**
     * Get the date attribute and format it as the time.
     *
     * @return string
     */
    public function getFormattedStartTimeAttribute() {
        return $this->date->format('g:ia');
    }

    /**
     * Return the ticket price in dollars to two decimal places.
     *
     * @return float
     */
    public function getTicketPriceInDollarsAttribute() {
        return  number_format($this->ticket_price / 100, 2);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders() {
        return $this->hasMany(Order::class);
    }

    /**
     * Creates a new order of tickets for the specified user and amount.
     *
     * @param   String  $email           Email of the user buying the tickets.
     * @param   Integer $ticketQuantity  Quantity of tickets to order.
     * @return  Model   Order            Returns the orders object.
     */
    public function orderTickets($email, $ticketQuantity) {
        $order = $this->orders()->create([
            'email'     => $email,
        ]);

        foreach (range(1, $ticketQuantity) as $i) {
            $order->tickets()->create([]);
        }

        return $order;
    }


}
