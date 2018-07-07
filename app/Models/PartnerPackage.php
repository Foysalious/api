<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerPackage extends Model
{
    protected $guarded = ['id'];
    protected $table = 'partner_subscription_packages';
    protected $dates = ['activate_from'];
}
