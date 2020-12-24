<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $guarded = ['id'];

    public function scopeApp($query, $app)
    {
        return $query->where('tag', $app);
    }

    /**
     * Does have versions greater than given version,
     * And is there a version from what(afterwards) it will be applicable.
     *
     * @param $query
     * @param $version
     * @return mixed
     */
    public function scopeVersion($query, $version)
    {
        return $query
            ->where('version_code', '>', $version)
            ->where(function ($query) use ($version) {
                $query->where('lowest_upgradable_version_code', '<', $version)
                    ->orWhereNull('lowest_upgradable_version_code');
            });
    }

    public function scopeCritical($query)
    {
        return $query->where('is_critical', 1);
    }
}
