<?php namespace App\Sheba\Notification\Customer;

use App\Models\MovieTicketOrder;

class MovieTicket extends NotificationHandler
{
    public function getNotification()
    {
        $movie_ticket_orders = MovieTicketOrder::where([
            ['agent_type', "App\\Models\\Customer"],
            ['agent_id', (int)$this->customer->id],
        ])->select('id', 'agent_type', 'agent_id', 'status', 'created_at', 'updated_at')
            ->orderBy('updated_at', 'DESC')
            ->limit(30)
            ->get();

        foreach ($movie_ticket_orders as $movie_ticket_order) {
            $icon = null;
            $text = null;
            if ($movie_ticket_order->status == 'confirmed') {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/served.png';
                $text = 'Your purchase is successful';
            } elseif ($movie_ticket_order->status == 'initiated') {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/Inprocess.png';
                $text = 'Your purchase has been Initiated';
            } else {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/cancelled.png';
                $text = 'Your purchase is failed';
            }

            $notification = [
                'type' => 'movie_ticket',
                'type_id' => $movie_ticket_order->id,
                'text' => 'Movie Ticket',
                'image' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/movie_ticket.png',
                'updated_at' => $movie_ticket_order->updated_at->toDateTimeString(),
                'statuses' => [
                    [
                        'text' => $text,
                        'icon' => $icon,
                        'date' => $movie_ticket_order->updated_at ? $movie_ticket_order->updated_at->format("d M") . ' at ' . $movie_ticket_order->updated_at->format("h:i A") : $movie_ticket_order->created_at->format("d M") . ' at ' . $movie_ticket_order->created_at->format("h:i A")
                    ]
                ]
            ];
            array_push($this->notifications, $notification);
        }
        return $this->notifications;
    }
}