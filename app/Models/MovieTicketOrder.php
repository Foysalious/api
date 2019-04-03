<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieTicketOrder extends Model
{
    protected $guarded = ['id'];

    public function isFailed()
    {
        return $this->status == 'Failed';
    }

    public function isSuccess()
    {
        return $this->status == 'Success';
    }
}
