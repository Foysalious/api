<?php


namespace Sheba\EMI;


use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sheba\Loan\DS\ReflectionArray;

class Item implements Arrayable {
    use ReflectionArray;
    protected $id, $customer_name, $customer_mobile, $created_at, $amount, $date;

    /**
     * Get the instance as an array.
     *
     * @return array
     * @throws \ReflectionException
     */
    public function toShort() {
        $data = $this->toArray();
        return $data;
    }

    public function toDetails() {

    }

    public function toDummy() {
        $today  = Carbon::now();
        $date   = rand(1, $today->day);
        $month  = rand(1, $today->month);
        $hour   = rand(0, 23);
        $minute = rand(0, 59);
        $sec    = rand(0, 59);
        $time   = Carbon::parse("2020-$month-$date $hour:$minute:$sec");
        return [
            'id'              => $this->id,
            'customer_name'   => 'George Di*****son',
            'amount'          => 4999.02,
            'created_at'      => $time->format('Y-m-d H:s:i'),
            'date'            => $time->format('Y-m-d'),
            'customer_mobile' => '+8801717588445'
        ];
    }
}
