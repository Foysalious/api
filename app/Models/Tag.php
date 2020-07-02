<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = ['id'];

    public function customers()
    {
        return $this->morphedByMany(Customer::class, 'taggable');
    }

    public function services()
    {
        return $this->morphedByMany(Service::class, 'taggable');
    }

    public function partnerTransactions()
    {
        return $this->morphedByMany(PartnerTransaction::class, 'taggable');
    }

    public function procurements()
    {
        return $this->morphedByMany(Procurement::class, 'taggable');
    }

    public function taggables()
    {
        return $this->hasMany(Taggable::class);
    }

    public function scopeOf($query, $taggable)
    {
        return $query->where('taggable_type', get_class($taggable))->get()->pluck('name', 'id');
    }

    public static function sync($tags, $taggable_type)
    {
        $tag_ids = [];
        foreach ($tags as $tag) {
            $existing_tag = Tag::select('id')->where('name', $tag)->where('taggable_type', $taggable_type)->first();
            $tag_id = $existing_tag ? $existing_tag->id : Tag::insertGetId([
                'name' => $tag,
                'taggable_type' => $taggable_type
            ]);
            $tag_ids[] = $tag_id;
        }
        return $tag_ids;
    }
}
