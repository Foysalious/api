<?php

namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Database\QueryException;
use Sheba\Dal\Accessor\Model as Accessor;

class CommentRepository
{
    private $morphable;
    private $morphable_id;
    private $created_by;
    private $model_name = 'App\Models\\';

    public function __construct($morphable, $morphable_id, $created_by)
    {
        $this->morphable = $this->model_name . $morphable;
        $this->morphable_id = $morphable_id;
        $this->created_by = $created_by;
    }

    public function store($comment)
    {
        $comment = new Comment(['comment' => $comment]);
        try {
            $comment->commentable_type = $this->morphable;
            $comment->commentable_id = $this->morphable_id;
            $comment->commentator_type = $this->model_name . class_basename($this->created_by);
            $comment->commentator_id = $comment->created_by = $this->created_by->id;
            $comment->created_by_name = 'Resource -' . $this->created_by->profile->name;
            $comment->save();
            $comment->accessors()->attach((Accessor::where('model_name', $comment->commentator_type))->first()->id);
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return false;
        }
        return $comment;
    }

}