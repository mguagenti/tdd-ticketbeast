<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\NotEnoughTicketsException;

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
     * Get only the published concerts.
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders() {
        return $this->belongsToMany(Order::class, 'tickets');
    }

    /**
     * @param $customerEmail
     * @return bool
     */
    public function hasOrderFor($customerEmail) {
        return  $this->orders()->where('email', $customerEmail)->count() > 0;
    }

    /**
     * @param $customerEmail
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function ordersFor($customerEmail) {
        return $this->orders()->where('email', $customerEmail)->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets() {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Creates a new order of tickets for the specified user and amount.
     *
     * @param   String  $email           Email of the user buying the tickets.
     * @param   Integer $ticketQuantity  Quantity of tickets to order.
     * @return  Model   Order            Returns the orders object.
     */
    public function orderTickets($email, $ticketQuantity) {
        $tickets = $this->findTickets($ticketQuantity);

        return $this->createOrder($email, $tickets);
    }

    /**
     * @param $email
     * @param $tickets
     * @return Model
     */
    public function createOrder($email, $tickets) {
        return Order::forTickets($tickets, $email, $tickets->sum('price'));
    }

    /**
     * @param $quantity
     * @return mixed
     */
    public function findTickets($quantity) {
        $tickets = $this->tickets()->available()
            ->take($quantity)
            ->get();

        if ($tickets->count() < $quantity) {
            throw new NotEnoughTicketsException;
        }

        return $tickets;
    }

    /**
     * Adds tickets that can be purchased to the Concert.
     *
     * @param   Integer $quantity   The quantity of tickets to create.
     * @return  Model   Concert     The Concert object.
     */
    public function addTickets($quantity) {
        foreach (range(1, $quantity) as $i) {
            $this->tickets()->create([]);
        }

        return $this;
    }

    /**
     * Returns the quantity of tickets remaining for a concert.
     *
     * @return int  The total number of tickets available for purchase.
     */
    public function ticketsRemaining() {
        return $this->tickets()->available()->count();
    }


}
