<?php

namespace App\Billing;

/**
 * Class FakePaymentGateway
 * @package App\Billing
 */
class FakePaymentGateway implements PaymentGateway {

    /**
     * @var \Illuminate\Support\Collection
     */
    private $charges;

    public function __construct() {
        $this->charges = collect();
    }

    /**
     * @return string
     */
    public function getValidTestToken() {
        return "valid-token";
    }

    /**
     * @param $amount
     * @param $token
     */
    public function charge($amount, $token) {
        if ($token !== $this->getValidTestToken()) {
            throw new PaymentFailedException;
        }
        $this->charges[] = $amount;
    }

    /**
     * @return mixed
     */
    public function totalCharges() {
        return $this->charges->sum();
    }


}