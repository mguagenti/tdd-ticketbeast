<?php

namespace Tests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Console\Kernel;
use App\Exceptions\Handler;

trait CreatesApplication {

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication() {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        Hash::driver('bcrypt')->setRounds(4);

        return $app;
    }

    /**
     * Helper class to disable the default exception handler
     * for more detailed exception messages.
     */
    protected function disableExceptionHandling() {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {
                //
            }

            public function report(\Exception $e) {
                //
            }

            public function render($request, \Exception $e) {
                throw $e;
            }
        });
    }


}
