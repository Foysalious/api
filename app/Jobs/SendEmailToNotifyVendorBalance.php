<?php

namespace App\Jobs;

use App\Jobs\Job;

use App\Models\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class SendEmailToNotifyVendorBalance extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    const QUEUE_NAME = 'ticket_vendor_balance_alert';


    private $vendor;
    private $redisName = 'ticket_maintenance_configuration';
    private $storage;
    private $configuration;

    /**
     * SendEmailToNotifyVendorBalance constructor.
     * @param $vendor
     */
    public function __construct($vendor)
    {
        $this->vendor = $vendor;
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
        $this->storage = Cache::store('redis');
        try {
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
