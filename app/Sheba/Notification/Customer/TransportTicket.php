<?php namespace App\Sheba\Notification\Customer;

use App\Models\Transport\TransportTicketOrder;

class TransportTicket extends NotificationHandler
{
    public function getNotification()
    {
        $transport_ticket_orders = TransportTicketOrder::where([
            ['agent_type', "App\\Models\\Customer"],
            ['agent_id', (int)$this->customer->id],
        ])->select('id', 'agent_type', 'agent_id', 'status', 'created_at', 'updated_at')->orderBy('updated_at', 'DESC')->get();

        foreach ($transport_ticket_orders as $transport_ticket_order) {
            $icon = null;
            if ($transport_ticket_order->status == 'confirmed') {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/served.png';
            } elseif ($transport_ticket_order->status == 'initiated') {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/Inprocess.png';
            } else {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/cancelled.png';
            }

            $notification = [
                'type' => 'transport_ticket',
                'type_id' => $transport_ticket_order->id,
                'text' => 'Transport Ticket',
                'image' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/bus_ticket.png',
                'created_at' => $transport_ticket_order->created_at->toDateTimeString(),
                'statuses' => [
                    [
                        'text' => "Your top up is $transport_ticket_order->status",
                        'icon' => $icon,
                        'date' => $transport_ticket_order->updated_at ? $transport_ticket_order->updated_at->format("d M") . ' at ' . $transport_ticket_order->updated_at->format("h:i A") : $transport_ticket_order->created_at->format("d M") . ' at ' . $transport_ticket_order->created_at->format("h:i A")
                    ]
                ]
            ];
            array_push($this->notifications, $notification);
        }
        return $this->notifications;
    }
}