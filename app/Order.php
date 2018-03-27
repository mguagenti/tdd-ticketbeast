<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets() {
        return $this->hasMany(Ticket::class);
    }


}
