<?php namespace App\Sheba\Comment;

use App\Models\Bid;
use App\Models\Business;
use App\Models\Partner;
use App\Models\Procurement;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Comment;
use DB;
use Sheba\Notification\NotificationCreated;

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
                $this->sendNotification();
            });
        } catch (QueryException $e) {
            return false;
        }
        return $comment;
    }

    private function sendNotification()
    {
        if ($this->commentableModel instanceof Procurement) {
            $message = $this->commentatorModel->name . ' has made a comment on #' . $this->commentableModel->id;
            $bid = $this->commentableModel->getActiveBid();
            $partner = $bid->bidder;
            if ($this->commentatorModel instanceof Business) {
                notify()->partner($partner)->send([
                    'title' => $message,
                    'type' => 'warning',
                    'event_type' => get_class($this->commentableModel),
                    'event_id' => $this->commentableModel->id,
                    'link' => config('sheba.partners_url') . "/" . $partner->sub_domain . "/procurements/" . $this->commentableModel->id
                ]);
            } elseif ($this->commentatorModel instanceof Partner) {
                foreach ($this->commentableModel->owner->superAdmins as $member) {
                    notify()->member($member)->send([
                        'title' => $message,
                        'type' => 'warning',
                        'event_type' => get_class($this->commentableModel),
                        'event_id' => $this->commentableModel->id,
                        'link' => config('sheba.business_url') . '/dashboard/procurement/orders/' . $this->commentableModel->id . '?bid=' . $bid->id
                    ]);
                }
            }
        } elseif ($this->commentableModel instanceof Bid) {
            $message = $this->commentatorModel->name . ' has made a comment on #' . $this->commentableModel->id;
            $bid = $this->commentableModel;
            $partner = $bid->bidder;
            if ($this->commentatorModel instanceof Business) {
                notify()->partner($partner)->send([
                    'title' => $message,
                    'type' => 'warning',
                    'event_type' => get_class($this->commentableModel),
                    'event_id' => $this->commentableModel->id,
                    'link' => config('sheba.partners_url') . "/" . $partner->sub_domain . "/bids/" . $this->commentableModel->id
                ]);
            } elseif ($this->commentatorModel instanceof Partner) {
                foreach ($bid->procurement->owner->superAdmins as $member) {
                    notify()->member($member)->send([
                        'title' => $message,
                        'type' => 'warning',
                        'event_type' => get_class($this->commentableModel),
                        'event_id' => $this->commentableModel->id,
                        'link' => config('sheba.business_url') . '/dashboard/procurement/' . $this->commentableModel->id . '/messaging?id=' . $partner->id
                    ]);
                }
            }
        }

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