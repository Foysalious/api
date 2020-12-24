<?php namespace Sheba\FraudDetection;

use App\Models\Comment;
use Sheba\Comment\Commentable;
use Sheba\Comment\FraudAlertNotificationHandler;
use Sheba\FraudDetection\Repository\AlertRepository;

class FraudAlert implements Commentable
{
    /** @var AlertRepository $alertRepo */
    private $alertRepo;
    public $id;

    public function __construct(AlertRepository $alert_repo)
    {
        $this->alertRepo = $alert_repo;
    }

    public function find($alert_id)
    {
        $alert_data = $this->alertRepo->getAlertDetail($alert_id);
        $this->id = $alert_data['id'];

        return $this;
    }

    public function comments()
    {
        return Comment::where("commentable_type", __CLASS__)->where('commentable_id', $this->id);
    }

    /**
     * @inheritDoc
     */
    public function getComments()
    {
        return $this->comments()->get();
    }

    /**
     * @inheritDoc
     */
    public function getNotificationHandlerClass()
    {
        return FraudAlertNotificationHandler::class;
    }
}
