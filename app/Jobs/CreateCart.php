<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CreateCart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $customerID;
    private $cart;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($customerID,$cart)
    {
        $this->customerID = $customerID;
        $this->cart = $cart;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::table('customers_cart')
        ->insert([
            'customer_id'    => $this->customerID,
            'cart_token'     => $this->cart,
        ]);
    }
}
