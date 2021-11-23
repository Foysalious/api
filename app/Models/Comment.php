<?php namespace App\Models;

use Sheba\Dal\Accessor\Model as Accessor;
use Sheba\Dal\BaseModel;

class Comment extends BaseModel
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
