<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoinRequest extends Model
{
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function organization()
    {
        return $this->morphTo();
    }

    public function requestor()
    {
        return ($this->requestor_type == 'App\Models\Profile') ?  $this->profile : $this->organization;
    }
}
