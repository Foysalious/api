<?php namespace App\Sheba\Comment;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Comment;
use DB;

class Comments
{
    private $commentableModel;
    private $commentatorModel;
    private $comment;
    private $model_name = 'App\Models\\';
    private $requestedData;
    private $createdBy;

    public function setRequestData(Request $request)
    {
        $this->requestedData = $request;
        return $this;
    }

    public function setCommentableModel($commentable_model)
    {
        $this->commentableModel = $commentable_model;
        return $this;
    }

    public function setCommentatorModel($commentator)
    {
        $this->commentatorModel = $commentator;
        return $this;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    public function formatData()
    {
        if ($this->requestedData->has('manager_resource')) {
            $this->createdBy = $this->requestedData->manager_resource;
        } elseif ($this->requestedData->has('manager_member')) {
            $this->createdBy = $this->requestedData->manager_member;
        }
        return $this;
    }

    public function store()
    {
        $comment = new Comment();
        try {
            DB::transaction(function () use ($comment) {
                $comment->comment = $this->comment;
                $comment->commentable_type = $this->model_name . class_basename($this->commentableModel);
                $comment->commentable_id = $this->commentableModel->id;

                $comment->commentator_type = $this->model_name . class_basename($this->commentatorModel);
                $comment->commentator_id = $this->commentatorModel->id;
                $comment->created_by = $this->createdBy->id;
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
            if ($this->createdBy->profile) {
                return $this->createdBy->profile->name;
            } else {
                return $this->commentatorModel->getContactPerson();
            }

        } catch (QueryException $e) {
            return false;
        }
    }
}