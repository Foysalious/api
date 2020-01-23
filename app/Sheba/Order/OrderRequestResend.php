<?php namespace App\Sheba\Order;

use App\Models\Job;
use App\Models\Order;
use App\Models\PartnerOrder;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OrderRequestResend
{
    /** @var Order $order */
    private $order;

    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @throws GuzzleException
     */
    public function send()
    {
        /** @var PartnerOrder $partner_order */
        $partner_order = $this->order->partnerOrders->first();
        $job = $partner_order->jobs->first();
        $services = $this->getServices($job);
        $requests = $partner_order->partnerOrderRequests;
        $partners = count($requests) > 0 ? $requests->pluck('partner_id')->unique()->toArray() : [];
        $client = new Client();
        $response = $this->makeClientCall($client, $job, $partner_order, $services, $partners);
        if ($response && $response->code == 200) {
            $partner_order->partner_searched_count = (int)$partner_order->partner_searched_count + 1;
            $partner_order->update();
        }
    }

    /**
     * @param $job
     * @return array
     */
    private function getServices($job)
    {
        $services = [];
        foreach ($job->jobServices as $jobService) {
            $data = [];
            $data['id'] = $jobService->service_id;
            $data['quantity'] = (double)$jobService->quantity;
            $data['option'] = json_decode($jobService->option);
            if ($job->carRentalJobDetail) {
                $data['pick_up_location_geo'] = json_decode($job->carRentalJobDetail->pick_up_address_geo);
                if ($job->carRentalJobDetail->destination_address_geo) $data['destination_location_geo'] = json_decode($job->carRentalJobDetail->destination_address_geo);
            }
            array_push($services, $data);
        }
        return $services;
    }

    /**
     * @param Client $client
     * @param Job $job
     * @param PartnerOrder $partner_order
     * @param array $services
     * @param array $partners
     * @return mixed
     * @throws GuzzleException
     */
    private function makeClientCall(Client $client, Job $job, PartnerOrder $partner_order, array $services, array $partners)
    {
        $response = $client->request('GET', config('sheba.api_url') . '/v3/partners/send-order-requests', [
            'query' => [
                'date' => $job->schedule_date, 'time' => $job->preferred_time, 'address_id' => $this->order->delivery_address_id, 'partner_order_id' => $partner_order->id, 'services' => json_encode($services), 'partners' => json_encode($partners),
            ]
        ]);
        return json_decode($response->getBody());
    }
}
