<?php namespace Sheba\Business\BusinessTransaction;

use Excel as BusinessTransactionExcel;
use Carbon\Carbon;

class TransactionExcel
{
    private $transactionData;
    private $data = [];

    public function setTransactionData(array $transaction_data)
    {
        $this->transactionData = $transaction_data;
        return $this;
    }

    public function get()
    {
        $header = $this->getHeaders();
        $this->makeData();
        $file_name = Carbon::now()->format('Y-m-d') . '_transaction_report';
        BusinessTransactionExcel::create($file_name, function ($excel) use ($header, $file_name) {
            $excel->sheet($file_name, function ($sheet) use ($header) {
                $sheet->fromArray($this->data, null, 'A1', false, false);
                $sheet->prependRow($header);
                $sheet->freezeFirstRow();
                $sheet->cell('A1:H1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->getDefaultStyle()->getAlignment()->applyFromArray(
                    array('horizontal' => 'left')
                );
                #$sheet->setAutoSize(true);
            });
        })->export('xlsx');
    }

    /**
     * @return void
     */
    private function makeData()
    {
        foreach ($this->transactionData as $transaction_data) {
            $this->data[] = [
                'id' => $transaction_data['id'],
                'amount' => $transaction_data['amount'],
                'type' => $transaction_data['type'],
                'log' => $transaction_data['log'],
                'debit' => $transaction_data['debit'],
                'credit' => $transaction_data['credit'],
                'balance' => $transaction_data['balance'],
                'created_at' => $transaction_data['created_at']
            ];
        }

    }

    /**
     * @return string[]
     */
    private function getHeaders()
    {
        return [
            'Id', 'Amount', 'Type', 'Log', 'Debit', 'Credit', 'Balance', 'Created At',
        ];
    }
}