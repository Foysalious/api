<?php


namespace App\Http\Controllers;
use App\Jobs\SendEmailToNotifyVendorBalance;
use App\Models\MovieTicketOrder;
use Illuminate\Mail\Mailer;
use Sheba\MovieTicket\Vendor\BlockBuster\BlockBuster;
use Sheba\Transport\Bus\Vendor\VendorFactory;
//use Sheba\MovieTicket\Vendor\VendorFactory;
use Sheba\PushNotificationHandler;
use Sheba\Transport\Bus\ClientCalls\BdTickets as BdTicketsClientCall;
use Sheba\Transport\Bus\Vendor\BdTickets\BdTickets;

class TestController extends  Controller
{
    public function testPushNotification1(Mailer $mailer)
    {
        (new PushNotificationHandler())->send([
            'title'      => 'Top Up failed',
            'message'    => 'দুঃখিত! আপনার মোবাইল রিচার্জ 01620011019 সফল হয় নি, পুনরায় চেষ্টা করুন',
            'event_type' => 'topup_failed',
            'event_id'   => '39169',
            'link'       => 'topup_failed',
            "sound"      => "notification_sound",
            "channel_id" => 'affiliate_channel',
        ],'affiliate_dev_39169', 'affiliate_channel', 'default');


        dd('success');

    }
    public function testPushNotification2(Mailer $mailer)
    {


        (new PushNotificationHandler())->send([
            'title'      => 'Movie Ticket purchase failed',
            'message'    =>  'দুঃখিত বন্ধু! আপনার মুভি টিকেট সফল ভাবে কাটা হয় নি 01620011019, পুনরায় চেষ্টা করুন।',
            'event_type' => 'movie_ticket_purchase_failed',
            'event_id'   => '39169',
            'link'       => 'movie_ticket_purchase_failed',
            "sound"      => "notification_sound",
            "channel_id" => 'affiliate_channel',
        ],'affiliate_dev_39169', 'affiliate_channel', 'default');


        dd('success');

    }
    public function testPushNotification3test(Mailer $mailer)
    {

        (new PushNotificationHandler())->send([
            'title'      => 'Transport Ticket purchase failed',
            'message'    =>  'দুঃখিত বন্ধু! আপনার বাস টিকেট সফল ভাবে কাটা হয় নি 01620011019, পুনরায় চেষ্টা করুন। ।',
            'event_type' => 'bus_ticket_purchase_failed',
            'event_id'   => '39169',
            'link'       => 'bus_ticket_purchase_failed',
            "sound"      => "notification_sound",
            "channel_id" => 'affiliate_channel',
        ],'affiliate_dev_39169', 'affiliate_channel', 'default');
        dd('success');

    }

    public function test(Mailer $mailer)
    {
        /*$mailer->send('emails.notify-vendor-balance', ['current_balance' => 100, 'vendor_name' => 'test'], function ($m)  {
           $m->from('yourEmail@domain.com', 'Sheba.xyz');
           $m->to('shovan@sheba.xyz')->subject('Low Balance for testvendor');
       });*/
       // $movie_ticket_order = MovieTicketOrder::find(1);

        //$vendor = $vendor->getById(1);
       // $movie_ticket_order->vendor = $vendor;

        //dispatch(new SendEmailToNotifyVendorBalance('transport_ticket',1));
       try{
           (new SendEmailToNotifyVendorBalance('transport_ticket',1))->handle($mailer);

            dd('success');
        }catch (\Exception $e)
         {dd($e);
        }

    }

}