<?php namespace Sheba\Business;

use App\Models\Business;
use App\Models\BusinessTransaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Sheba\Helpers\TimeFrame;

class TransactionReportExcel implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /** @var Business */
    private $business;
    /** @var TimeFrame */
    private $timeFrame;

    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    /**
     * @param TimeFrame $timeFrame
     * @return TransactionReportExcel
     */
    public function setTimeFrame(TimeFrame $timeFrame): TransactionReportExcel
    {
        $this->timeFrame = $timeFrame;
        return $this;
    }

    public function collection(): Collection
    {
        $query = $this->business->transactions();
        if (!is_null($this->timeFrame)) $query = $query->createdAtBetweenTimeFrame($this->timeFrame);

        return $query->get()->map(function (BusinessTransaction $transaction) {
            return [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'type' => $this->getEventType($transaction),
                'log' => $transaction->log,
                'debit' => $transaction->isDebit() ? $transaction->amount : '',
                'credit' => $transaction->isCredit() ? $transaction->amount : '',
                'balance' => $transaction->balance,
                'created_at' => $transaction->created_at->toDateTimeString(),
            ];
        });
    }

    private function getEventType(BusinessTransaction $transaction)
    {
        if (str_contains($transaction->log, "topped up")) $event_type = "Top Up";
        elseif (strContainsAll($transaction->log, ["recharge", "failed", "refunded"])) $event_type = "Top Up Refund";
        elseif ($transaction->isCredit()) $event_type = "Cash in";
        elseif ($transaction->isDebit()) $event_type = "Purchase";
        else $event_type = "N/F";
        return $event_type;
    }

    public function headings(): array
    {
        return [ 'Id', 'Amount', 'Type', 'Log', 'Debit', 'Credit', 'Balance', 'Created At' ];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}
