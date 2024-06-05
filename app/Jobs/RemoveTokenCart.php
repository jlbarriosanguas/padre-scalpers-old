<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RemoveTokenCart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $customerID;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($customerID)
    {
        $this->customerID = $customerID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::table('customers_cart')
            ->where('customer_id',$this->customerID)
            ->update([
                'cart_token'     => null,
            ]);
    }
}
