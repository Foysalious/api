<?php namespace Sheba\Reports\Customer;

use App\Http\Requests\Reports\ReportStartEndRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Sheba\Reports\ExcelHandler;
use Mail;

class MailToMkt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheba:send-customer-report-to-mkt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send yesterday registered customer report to mkt.';

    private $omor = 'omersharif@gmail.com';
    private $cto = 'shoaib.startern@gmail.com';
    private $shafiq = 'shafiq.startern@gmail.com';

    /**
     * Execute the console command.
     * @param CustomerNormalData $data
     * @param ExcelHandler $excel
     * @throws \Exception
     */
    public function handle(CustomerNormalData $data, ExcelHandler $excel)
    {
        $request = new ReportStartEndRequest();
        $request->replace([
            'start_date' => Carbon::yesterday()->toDateString(),
            'end_date' => Carbon::today()->subSecond()->toDateString(),
            'is_advanced' => true
        ]);

        $excel->setName('Customer');
        $excel->setViewFile('customer');
        $excel->createReport($data->setRequest($request)->get());
        $file_name = $excel->save();

        Mail::raw('Yesterday\'s registered customers', function ($message) use ($file_name) {
            if(config('mail.drivers.mailgun.domain') == 'mg.sheba.xyz') {
                $message->to($this->cto)->cc($this->shafiq);
            }
            $message->attach($file_name);
        });

        unlink($file_name);
        $this->info('Done');
    }
}