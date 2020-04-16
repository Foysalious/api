<?php namespace Sheba\EMI;


use Carbon\Carbon;

class Repository extends ClientRepository {
    private $partner;

    public function getRecent() {
        return [
            (new Item(['id' => 1]))->toDummy(),
            (new Item(['id' => 2]))->toDummy(),
            (new Item(['id' => 3]))->toDummy(),
        ];
    }

    public function get() {
        $items  = collect();
        $dItems = collect();
        foreach (range(1, 10) as $index) {
            $items->push((new Item(['id' => $index]))->toDummy());
        }
        $dateWise = $items->groupBy('date')->toArray();
        foreach ($dateWise as $key => $dItem) {
            $dItems->push(['date' => $key, 'items' => $dItem]);
        }
        return $dItems;
    }

    public function details($id) {
        $type   = ['PosOrder', 'EMI'];
        $iPayer = ['Customer', 'Partner'];
        shuffle($type);
        shuffle($iPayer);
        return [
            'id'                  => $id,
            'status'              => 'paid',
            'customer_name'       => 'George Di*****son',
            'customer_mobile'     => '+8801717588445',
            'method'              => 'online',
            'amount'              => 4999.02,
            'type'                => $type[0],
            'interest_payer'      => $iPayer[0],
            'interest_payer_name' => 'George Di*****son',
            'created_at'          => Carbon::now()->format('Y-m-d H:s:i'),
            'payment_id'          => $id
        ];
    }
}
