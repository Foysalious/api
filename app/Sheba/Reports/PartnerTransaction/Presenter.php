<?php namespace Sheba\Reports\PartnerTransaction;

use App\Models\PartnerTransaction;
use Sheba\Reports\Presenter as BasePresenter;

class Presenter extends BasePresenter
{
    /** @var PartnerTransaction */
    protected $partnerTransaction;

    protected $fields = [
        'id' => 'ID',
        'date' => 'Date',
        'partner_id' => 'Partner ID',
        'partner_name' => 'Partner Name',
        'description' => 'Description',
        'gateway' => 'Gateway',
        'gateway_transaction_id' => 'Gateway Transaction ID',
        'sender' => 'Sender',
        'tags' => 'Tags',
        'requested_by' => 'Requested By',
        'debit' => 'Debit',
        'credit' => 'Credit',
        'balance' => 'Balance'
    ];

    public function setPartnerTransaction(PartnerTransaction $transaction)
    {
        $this->partnerTransaction = $transaction;
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        $data = [
            'id' => $this->partnerTransaction->id,
            'date' => $this->partnerTransaction->created_at,
        ];
        if($this->partnerTransaction->partner_name) {
            $data['partner_id'] = $this->partnerTransaction->partner_id;
            $data['partner_name'] = $this->partnerTransaction->partner_name;
        }
        $data += [
            'description' => $this->partnerTransaction->log,
            'gateway' => $this->partnerTransaction->gateway,
            'gateway_transaction_id' => $this->partnerTransaction->gateway_transaction_id,
            'sender' => $this->partnerTransaction->sender,
            'tags' => $this->partnerTransaction->tags,
            'requested_by' => $this->partnerTransaction->created_by_name,
            'debit' => $this->partnerTransaction->type == 'Debit' ? $this->partnerTransaction->amount : null,
            'credit' => $this->partnerTransaction->type == 'Credit' ? $this->partnerTransaction->amount : null,
        ];
        if($this->partnerTransaction->balance) {
            $data['balance'] = $this->partnerTransaction->balance;
        }
    }

    /**
     * @return array
     */
    public function getForView()
    {
        $data = $this->get();
        $view_data = [];
        foreach($this->fields as $key => $field) {
            if(array_key_exists($key, $data)) $view_data[$field] = $data[$key];
        }
        $view_data['Date'] = $view_data['Date']->format('d M, Y h:i A');
        $view_data['Gateway'] = $view_data['Gateway'] ?: '';
        $view_data['Gateway Transaction ID'] = $view_data['Gateway Transaction ID'] ?: '';
        $view_data['Tags'] = !empty($view_data['Tags']) ? implode(',', $view_data['tags']) : 'N/S';
        $view_data['Sender'] = $view_data['Sender'] ? "`{$view_data['Sender']}`" : '';
        $view_data['Debit'] = $view_data['Debit'] ?: '';
        $view_data['Credit'] = $view_data['Credit'] ?: '';
        return $view_data;
    }
}
