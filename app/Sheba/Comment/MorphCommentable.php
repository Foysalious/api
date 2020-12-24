<?php namespace Sheba\Comment;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface MorphCommentable extends Commentable
{
    /** @return MorphMany */
    public function comments();
}
