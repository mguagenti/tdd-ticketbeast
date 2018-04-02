<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    /**
     * @var array
     */
    protected $guarded = [];

    public static function forTickets($tickets, $email, $amount) {
        $order = self::create([
            'email'  => $email,
            'amount' => $amount
        ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function concert() {
        return $this->belongsTo(Concert::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets() {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Cancels an order and sets the order ID to null.
     */
    public function cancel() {
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }

        $this->delete();
    }

    /**
     * @return int
     */
    public function ticketQuantity() {
        return $this->tickets()->count();
    }

    /**
     * @return array
     */
    public function toArray() {
        return [
            'email'             => $this->email,
            'ticket_quantity'   => $this->ticketQuantity(),
            'amount'            => $this->amount,
        ];
    }


}
