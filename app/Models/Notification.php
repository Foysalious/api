<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = ['id'];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function scopeSeen($query)
    {
        return $query->where('is_seen', 1);
    }

    public function scopeUnseen($query)
    {
        return $query->where('is_seen', 0);
    }

    public function scopeSortLatest($query)
    {
        return $query->orderBy('created_at', 'DESC');
    }

    public function seen()
    {
        $this->is_seen = true;
        $this->timestamps = false;
        $this->save();
    }

    public function getType()
    {
        $type = strtolower($this->event_type);
        if (stripos($type, 'support')) return 'support';
        if (stripos($type, 'announcement')) return 'announcement';
        elseif (stripos($type, 'vehicle')) return 'vehicle';
        elseif (stripos($type, 'driver')) return 'driver';
        elseif (stripos($type, 'procurement')) return 'procurement';
        elseif (stripos($type, 'bid')) return 'bid';
        elseif (stripos($type, 'businesstriprequest')) return 'businesstriprequest';
    }
}
