<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Concert;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;

class ConcertOrdersController extends Controller {

    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway) {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId) {
        $this->validate(request(), [
            'email'             => 'required|email',
            'ticket_quantity'   => 'required|integer|min:1',
            'payment_token'     => 'required'
        ]);

        try {
            $concert = Concert::find($concertId);
            $ticketQuantity = request('ticket_quantity');
            $this->paymentGateway->charge($ticketQuantity * $concert->ticket_price, request('payment_token'));
            $concert->orderTickets(request('email'), $ticketQuantity);
            return response()->json([], 201);
        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        }
    }
}
