<?php namespace App\Sheba\Comment;

use Illuminate\Database\QueryException;
use App\Models\Comment;
use DB;

class Comments
{
    private $commentableType;
    private $commentableId;
    private $commentableModel;
    private $commentatorType;
    private $commentatorId;
    private $commentatorModel;
    private $comment;
    private $model_name = 'App\Models\\';

    public function setCommentableType($commentable_type)
    {
        $this->commentableType = $this->model_name . ucfirst(camel_case($commentable_type));
        return $this;
    }

    public function setCommentableId($commentable_id)
    {
        $this->commentableId = (int)$commentable_id;
        return $this;
    }

    public function getCommentableId()
    {
        return $this->commentableId;
    }

    public function getCommentableModel()
    {
        $this->commentableModel = app($this->commentableType)::findOrFail($this->commentableId);
        return $this->commentableModel;
    }


    public function setCommentatorType($commentator_type)
    {
        $this->commentatorType = $this->model_name . ucfirst(camel_case($commentator_type));
        return $this;
    }

    public function setCommentatorId($commentator_id)
    {
        $this->commentatorId = (int)$commentator_id;
        return $this;
    }

    public function getCommentatorId()
    {
        return $this->commentatorId;
    }

    public function getCommentatorModel()
    {
        $this->commentatorModel = app($this->commentatorType)::findOrFail($this->commentatorId);
        return $this->commentatorModel;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    public function store()
    {
        $comment = new Comment();
        try {
            DB::transaction(function () use ($comment) {
                $comment->comment = $this->comment;
                $comment->commentable_type = $this->commentableType;
                $comment->commentable_id = $this->commentableId;
                $comment->commentator_type = $this->commentatorType;
                $comment->commentator_id = $this->commentatorId;
                $comment->created_by = $this->getCommentator()->id;
                $comment->created_by_name = $this->getCommentatorName();
                $comment->save();
            });
        } catch (QueryException $e) {
            return false;
        }
        return $comment;
    }

    private function getCommentatorName()
    {
        try {
            if ($this->commentatorModel->profile) {
                return $this->commentatorModel->profile->name;
            } else {
                return $this->commentatorModel->getContactPerson();
            }

        } catch (QueryException $e) {
            return false;
        }
    }

    private function getCommentator()
    {
        try {
            return $this->commentatorModel->getAdmin();
        } catch (QueryException $e) {
            return false;
        }
    }
}