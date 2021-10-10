<?php

namespace App\Jobs;

use App\Jobs\Job;

use App\Jobs\TicketVendorBalanceAlert\Movie;
use App\Jobs\TicketVendorBalanceAlert\Transport;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Foundation\Application;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class SendEmailToNotifyVendorBalance extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    const QUEUE_NAME = 'ticket_vendor_balance_alert';


    private $vendor_id;
    private $redisName = 'ticket_maintenance_configuration';
    private $storage;
    private $configuration;
    private $type;
    private $vendor;

    /**
     * SendEmailToNotifyVendorBalance constructor.
     * @param $type
     * @param $vendor_id
     */
    public function __construct($type,$vendor_id)
    {
        $this->vendor_id = $vendor_id;
        $this->type = $type;
        $this->queue = self::QUEUE_NAME;
    }

    /**
     * Execute the job.
     *
     * @param Mailer $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {

        try {
            $this->getVendor();
            $this->storage = Cache::store('redis');
            $this->getConfiguration();
            $balance_threshold = $this->configuration['balance_threshold'];

            $balance = $this->vendor->balance();

            if ($balance < $balance_threshold) {
                $users = $this->notifiableUsers();
                foreach ($users as $user) {

                    $mailer->send('emails.notify-vendor-balance', ['current_balance' => $balance, 'vendor_name' => (new \ReflectionClass($this->vendor))->getShortName()], function ($m) use ($user) {
                        $m->from('yourEmail@domain.com', 'Sheba.xyz');
                        $m->to($user->email)->subject('Low Balance for ' . (new \ReflectionClass($this->vendor))->getShortName());
                    });
                }
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }

    }

    /**
     * @return $this
     */
    private function getVendor()
    {

        try{
            if($this->type == 'movie_ticket')
                $this->vendor = (new Movie())->getVendor($this->vendor_id);
            else if($this->type == 'transport_ticket'){
                $this->vendor = (new Transport())->getVendor($this->vendor_id);
            }

        }catch (\Exception $e){
        }


        return $this;
    }

    /**
     * @return mixed
     */
    private function notifiableUsers()
    {
        return User::whereIn('id', $this->configuration['notifiable_users'])->get();
    }

    /**
     * @return Application|mixed
     */
    private function getConfiguration()
    {
        if ($this->storage->has($this->redisName)) {
            $data = $this->storage->get($this->redisName);
            if(empty($data['notifiable_users'])){
                $data['notifiable_users'] = config('ticket')['notifiable_users'];
            }
        } else {
            $data = config('ticket');
            $this->storage->forever($this->redisName, $data);
        }

        return $this->configuration = $data;

    }
}
