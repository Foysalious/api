<?php namespace Sheba\Comment;

use App\Models\Comment;

trait MorphComments
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * @inheritDoc
     */
    public function getComments()
    {
        return $this->comments;
    }
}
