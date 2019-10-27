<?php namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Database\QueryException;
use Sheba\Dal\Accessor\Model as Accessor;
use DB;

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
            DB::transaction(function () use ($comment) {
                $comment->commentable_type = $this->morphable;
                $comment->commentable_id = $this->morphable_id;
                $comment->commentator_type = $this->model_name . class_basename($this->created_by);
                $comment->commentator_id = $comment->created_by = $this->created_by->id;
                $comment->created_by_name = class_basename($this->created_by) . ' -' . $this->getCommentatorName();
                $comment->save();
                $comment->accessors()->attach((Accessor::where('model_name', $comment->commentator_type))->first()->id);
            });
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return false;
        }
        return $comment;
    }

    private function getCommentatorName()
    {
        try {
            if ($this->created_by->profile) {
                return $this->created_by->profile->name;
            } else {
                return $this->created_by->getContactPerson();
            }

        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }

}