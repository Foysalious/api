<?php namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;

class TicketTransaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
}