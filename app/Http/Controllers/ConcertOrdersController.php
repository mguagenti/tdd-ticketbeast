<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Concert;
use App\Order;
use App\Exceptions\NotEnoughTicketsException;
use App\Reservation;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;

class ConcertOrdersController extends Controller {

    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway) {
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Orders tickets and returns the tickets as a JSON array.
     *
     * @param $concertId                        The ID of the concert to order tickets from.
     * @return \Illuminate\Http\JsonResponse    The status of the order.
     */
    public function store($concertId) {
        $this->validate(request(), [
            'email'             => 'required|email',
            'ticket_quantity'   => 'required|integer|min:1',
            'payment_token'     => 'required'
        ]);

        $concert = Concert::published()->findOrFail($concertId);

        try {
            $tickets = $concert->findTickets(request('ticket_quantity'));
            $reservation = new Reservation($tickets);

            $this->paymentGateway->charge($reservation->totalCost(), request('payment_token'));

            $order = Order::forTickets($tickets, request('email'), $tickets->sum('price'));

            return response()->json($order, 201);

        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }


}
