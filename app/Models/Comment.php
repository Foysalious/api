<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Accessor\Model as Accessor;

class Comment extends Model
{
    protected $guarded = ['id'];

    public function writer()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function commentator()
    {
        return $this->morphTo();
    }

    public function accessors()
    {
        return $this->belongsToMany(Accessor::class, 'accessor_comment', 'comment_id', 'accessor_id');
    }
}
