<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Comment\MorphCommentable;
use Sheba\Comment\MorphComments;

class InspectionItemIssue extends Model implements MorphCommentable
{
    use MorphComments;

    protected $guarded = ['id'];
    protected $table = 'inspection_item_issues';

    public function inspectionItem()
    {
        return $this->belongsTo(InspectionItem::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * @inheritDoc
     */
    public function getNotificationHandlerClass()
    {
        // TODO: Implement getNotificationHandlerClass() method.
    }
}
