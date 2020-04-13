<?php

namespace App\Jobs;

use App\Jobs\Job;

use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Sheba\MovieTicket\Vendor\BlockBuster\BlockBuster;
use SuperClosure\SerializableClosure;

class SendEmailToNotifyVendorBalance extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;


    private $vendor;
    private $redisName = 'ticket_maintenance_configuration';
    private $storage;
    private $configuration;

    /**
     * SendEmailToNotifyVendorBalance constructor.
     * @param $order
     */
    public function __construct()
    {

        $this->vendor  = new BlockBuster();
        $this->storage = Cache::store('redis');
    }


    /**
     * @param Mailer $mailer
     * @throws GuzzleException
     */
    public function handle(Mailer $mailer)
    {
        try {
            $this->getConfiguration();
            $balance_threshold = $this->configuration['balance_threshold'];
            $balance = $this->vendor->balance();
            if ($balance < $balance_threshold) {
                $users = $this->notifiableUsers();
                foreach ($users as $user) {

                    $mailer->send('emails.notify-vendor-balance', ['current_balance' => $balance, 'vendor_name' => (new \ReflectionClass($this->vendor))->getShortName()], new SerializableClosure(function ($m) use ($user) {
                        $m->from('yourEmail@domain.com', 'Sheba.xyz');
                        $m->to($user->email)->subject('Low Balance for ' . (new \ReflectionClass($this->vendor))->getShortName());
                    }));
                }
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }

    }

    private function notifiableUsers()
    {
        return User::whereIn('id', $this->configuration['notifiable_users'])->get();
    }

    private function getConfiguration()
    {
        if ($this->storage->has($this->redisName)) {
            $data = $this->storage->get($this->redisName);

        } else {
            $data = config('ticket');
            $this->storage->forever($this->redisName, $data);
        }

        return $this->configuration = $data;

    }
}
