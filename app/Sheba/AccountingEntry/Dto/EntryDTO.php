<?php

namespace App\Sheba\AccountingEntry\Dto;

class EntryDTO
{
    public $contact_id;
    public $contact_name;
    public $contact_mobile;
    public $contact_pro_pic;
    public $amount;
    public $source_type;
    public $account_key;
    public $contact_type;
    public $entry_at;
    public $attachments;
    public $note;
    public $created_from;
    public $source_id;
    public $debit_account_key;
    public $credit_account_key;
    public $amount_cleared;
    public $reconcile_amount;
    public $updated_entry_amount;
    public $inventory_products;
    public $total_discount;
    public $total_vat;
    public $delivery_charge;
    public $bank_transaction_charge;
    public $interest;
    public $details;
    public $reference;
    public $paid_by;
    public $is_due_tracker_payment_link;
    public $real_amount;

    /**
     * @param mixed $contact_id
     */
    public function setContactId($contact_id)
    {
        $this->contact_id = $contact_id;
        return $this;
    }

    /**
     * @param mixed $contact_name
     */
    public function setContactName($contact_name)
    {
        $this->contact_name = $contact_name;
        return $this;
    }

    /**
     * @param mixed $contact_mobile
     */
    public function setContactMobile($contact_mobile)
    {
        $this->contact_mobile = $contact_mobile;
        return $this;
    }

    /**
     * @param mixed $contact_pro_pic
     */
    public function setContactProPic($contact_pro_pic)
    {
        $this->contact_pro_pic = $contact_pro_pic;
        return $this;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $source_type
     */
    public function setSourceType($source_type)
    {
        $this->source_type = $source_type;
        return $this;
    }

    /**
     * @param mixed $account_key
     */
    public function setAccountKey($account_key)
    {
        $this->account_key = $account_key;
        return $this;
    }

    /**
     * @param mixed $contact_type
     */
    public function setContactType($contact_type)
    {
        $this->contact_type = $contact_type;
        return $this;
    }

    /**
     * @param mixed $entry_at
     */
    public function setEntryAt($entry_at)
    {
        $this->entry_at = $entry_at;
        return $this;
    }

    /**
     * @param mixed $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param mixed $created_from
     */
    public function setCreatedFrom($created_from)
    {
        $this->created_from = $created_from;
        return $this;
    }

    /**
     * @param mixed $source_id
     */
    public function setSourceId($source_id)
    {
        $this->source_id = $source_id;
        return $this;
    }

    /**
     * @param mixed $debit_account_key
     */
    public function setDebitAccountKey($debit_account_key)
    {
        $this->debit_account_key = $debit_account_key;
        return $this;
    }

    /**
     * @param mixed $credit_account_key
     */
    public function setCreditAccountKey($credit_account_key)
    {
        $this->credit_account_key = $credit_account_key;
        return $this;
    }

    /**
     * @param mixed $amount_cleared
     */
    public function setAmountCleared($amount_cleared)
    {
        $this->amount_cleared = $amount_cleared;
        return $this;
    }

    /**
     * @param mixed $reconcile_amount
     */
    public function setReconcileAmount($reconcile_amount)
    {
        $this->reconcile_amount = $reconcile_amount;
        return $this;
    }

    /**
     * @param mixed $updated_entry_amount
     */
    public function setUpdatedEntryAmount($updated_entry_amount)
    {
        $this->updated_entry_amount = $updated_entry_amount;
        return $this;
    }

    /**
     * @param mixed $inventory_products
     */
    public function setInventoryProducts($inventory_products)
    {
        $this->inventory_products = $inventory_products;
        return $this;
    }

    /**
     * @param mixed $total_discount
     */
    public function setTotalDiscount($total_discount)
    {
        $this->total_discount = $total_discount;
        return $this;
    }

    /**
     * @param mixed $total_vat
     */
    public function setTotalVat($total_vat)
    {
        $this->total_vat = $total_vat;
        return $this;
    }

    /**
     * @param mixed $delivery_charge
     */
    public function setDeliveryCharge($delivery_charge)
    {
        $this->delivery_charge = $delivery_charge;
        return $this;
    }

    /**
     * @param mixed $bank_transaction_charge
     */
    public function setBankTransactionCharge($bank_transaction_charge)
    {
        $this->bank_transaction_charge = $bank_transaction_charge;
        return $this;
    }

    /**
     * @param mixed $interest
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param mixed $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @param mixed $paid_by
     */
    public function setPaidBy($paid_by)
    {
        $this->paid_by = $paid_by;
        return $this;
    }

    /**
     * @param mixed $is_due_tracker_payment_link
     */
    public function setIsDueTrackerPaymentLink($is_due_tracker_payment_link)
    {
        $this->is_due_tracker_payment_link = $is_due_tracker_payment_link;
        return $this;
    }

    /**
     * @param mixed $real_amount
     */
    public function setRealAmount($real_amount)
    {
        $this->real_amount = $real_amount;
        return $this;
    }




}