<?php


namespace App\Jobs;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendTenderBillInvoiceEmailToBusiness extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;
    private $path;
    /**
     * Create a new job instance.
     *
     * @param Business $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        #dd($path);
        Mail::send([], [], function ($m){
            $m->to('asad.ahmed@shebaplatform.xyz')->subject('Email Testing');
            $m->setBody('<h1>'.$this->path.'</h1>', 'text/html');
            $m->attach($this->path);
        });
    }

}