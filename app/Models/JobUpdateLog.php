<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobUpdateLog extends Model
{
    protected $guarded = ['id'];

    public function getDecodedLogAttribute()
    {
        return json_decode($this->log, 1);
    }

    /**
     * @return bool
     */
    public function isScheduleChangeLog()
    {
        return $this->isScheduleDateChangeLog() || $this->isPreferredTimeChangeLog();
    }

    /**
     * @return bool
     */
    public function isScheduleChangeLogWithBothParams()
    {
        return $this->isScheduleDateChangeLog() && $this->isPreferredTimeChangeLog();
    }

    /**
     * @return bool
     */
    public function isScheduleDateChangeLog()
    {
        return $this->hasKey('schedule_date');
    }

    /**
     * @return bool
     */
    public function isPreferredTimeChangeLog()
    {
        return $this->hasKey('preferred_time');
    }

    /**
     * @param  $key
     * @return bool
     */
    public function hasKey($key)
    {
        return array_key_exists($key, $this->decoded_log);
    }
}
