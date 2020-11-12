<?php namespace Sheba\Comment;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

interface Commentable
{
    /** @return Builder */
    public function comments();

    /** @return Collection */
    public function getComments();

    /** @return string */
    public function getNotificationHandlerClass();
}
