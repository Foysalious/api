<?php namespace Sheba\CustomerDeliveryAddress;


class CustomerDeliveryAddressManager
{
    public function sortAddressesByHomeAndWork($customer_delivery_addresses)
    {
        $address_position = $this->findHomeAndWorkAddressPosition($customer_delivery_addresses);
        return $this->filterAddressByHomeAndWork($address_position, $customer_delivery_addresses);
    }

    /**
     * RETURN INDEX OF HOME AND WORK, IF PRESENT
     *
     * @param $delivery_addresses
     * @return mixed
     */
    private function findHomeAndWorkAddressPosition($delivery_addresses)
    {
        $address_position = ['home' => null, 'work' => null];
        foreach ($delivery_addresses as $index => $customer_delivery_address) {
            $address_name = strtolower($customer_delivery_address->name);
            if ($address_name == 'home') $address_position['home'] = $index;
            if ($address_name == 'work') $address_position['work'] = $index;
        }

        return $address_position;
    }

    /**
     * @param $address_position
     * @param $customer_delivery_addresses
     * @return mixed
     */
    private function filterAddressByHomeAndWork($address_position, $customer_delivery_addresses)
    {
        $home_address_index = $address_position['home'];
        $work_address_index = $address_position['work'];

        $home_address_element = !is_null($home_address_index) ? $customer_delivery_addresses[$home_address_index] : [];
        $work_address_element = !is_null($work_address_index) ? $customer_delivery_addresses[$work_address_index] : [];

        unset($customer_delivery_addresses[$home_address_index], $customer_delivery_addresses[$work_address_index]);
        array_unshift($customer_delivery_addresses, $home_address_element, $work_address_element);
        $customer_delivery_addresses = array_filter($customer_delivery_addresses);

        return collect($customer_delivery_addresses)->values()->all();
    }

    /**
     * @param $customer_order_addresses
     * @param $customer_delivery_address
     * @return int
     */
    public function getOrderCount($customer_order_addresses, $customer_delivery_address)
    {
        $count = 0;
        $customer_order_addresses->each(function ($customer_order_addresses) use ($customer_delivery_address, &$count) {
            similar_text($customer_delivery_address->address, $customer_order_addresses->delivery_address, $percent);
            if ($percent >= 80) $count = $customer_order_addresses->c;
        });
        return $count;
    }
}