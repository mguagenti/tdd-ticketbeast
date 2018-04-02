<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model {

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * Get only available tickets that have not already been purchased.
     *
     * @param $query
     * @return mixed
     */
    public function scopeAvailable($query) {
        return $query->whereNull('order_id');
    }

    /**
     * Release the ticket and set the order ID to null
     */
    public function release() {
        $this->update(['order_id' => null]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function concert() {
        return $this->belongsTo(Concert::class);
    }

    /**
     * Get the price of the ticket from the concert.
     *
     * @return mixed
     */
    public function getPriceAttribute() {
        return $this->concert->ticket_price;
    }


}
