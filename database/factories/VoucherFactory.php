<?php

namespace Database\Factories;

use App\Models\Voucher;
use Carbon\Carbon;

class VoucherFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Voucher::class;

    public function definition(): array
    {

        return [
            'code' => "xyz",
            'amount' => 50,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDay(2)->timestamp,
        ];


    }
}