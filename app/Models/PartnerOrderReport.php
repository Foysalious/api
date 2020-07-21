<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerOrderReport extends Model
{
    public $table = "partner_order_report";

    public $timestamps = false;

    public $guarded = [];

    protected $dates = [
        'order_first_created',
        'customer_registration_date',
        'cancelled_date',
        'closed_date',
        'closed_and_paid_date',
        'order_updated_at',
        'csat_date',
        'accept_date',
        'declined_date',
        'report_updated_at',
        'created_date',
        'request_created_date',
        'schedule_date'
    ];
}