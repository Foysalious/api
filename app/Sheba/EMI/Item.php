<?php


namespace Sheba\EMI;


use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sheba\Loan\DS\ReflectionArray;

class Item implements Arrayable {
    use ReflectionArray;
    protected $id, $customer_name, $customer_mobile, $created_at, $amount, $date, $entry_at, $party, $head, $interest, $bank_transaction_charge, $source_type, $source_id, $payment_id, $payment_method, $source, $amount_cleared, $customer;

    /**
     * Get the instance as an array.
     *
     * @return array
     * @throws \ReflectionException
     */
    public function toShort() {
        $this->setExtras();
        return [
            'id'              => $this->id,
            'customer_name'   => $this->customer_name,
            'customer_mobile' => $this->customer_mobile,
            'date'            => $this->date,
            'created_at'      => $this->created_at,
            'amount'          => number_format((double)$this->amount, 2),
            'status'          => $this->getStatus(),
            'head'            => $this->head,
            'entry_at'        => $this->entry_at
        ];
    }

    public function setExtras() {
        $this->date     = Carbon::parse($this->created_at)->format('Y-m-d');
        $this->customer = $this->getCustomer();
        if ($this->customer) {
            $this->customer_name   = $this->customer['name'];
            $this->customer_mobile = $this->customer['mobile'];
        };
    }

    private function getType() {
        if (!empty($this->source)) {
            return class_basename($this->source);
        }
        return "EMI";
    }

    private function setSource() {
        if (!empty($this->source_type) && !empty($this->source_id)) {
            try {
                $this->source = "App\\Models\\" . pamelCase($this->source_type)::find($this->source_id);
            } catch (\Throwable $e) {
                app('sentry')->captureException($e);
            }
        }
    }

    private function getStatus() {
        return round((double)$this->amount, 2) - round((double)($this->amount_cleared), 2) > 0 ? "due" : "paid";
    }

    public function toDetails() {
        $this->setExtras();
        $this->setSource();
        return [
            'id'                  => $this->id,
            'status'              => $this->getStatus(),
            'customer_name'       => $this->customer_name,
            'customer_mobile'     => $this->customer_mobile,
            'method'              => $this->payment_method,
            'amount'              => number_format((double)$this->amount, 2),
            'type'                => $this->getType(),
            'interest_payer'      => "Customer",
            'interest_payer_name' => $this->customer_name,
            'created_at'          => $this->created_at,
            'payment_id'          => $this->payment_id ?: $this->id
        ];
    }

    private function getCustomer() {
        if (isset($this->party) && isset($this->party['profile_id'])) {
            return Profile::select('name', 'mobile')->find($this->party['profile_id']);
        }
        return null;
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
