<?php namespace Sheba\Business\Issue;


use App\Models\InspectionItem;
use App\Models\InspectionItemIssue;
use Illuminate\Database\QueryException;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemStatusLogRepositoryInterface;
use Sheba\Repositories\Interfaces\IssueRepositoryInterface;
use DB;

class Creator
{
    /** @var InspectionItem */
    private $inspectionItem;
    private $issueRepository;
    private $inspectionItemStatusLogRepository;
    private $inspectionItemRepository;

    public function __construct(IssueRepositoryInterface $issue_repository, InspectionItemRepositoryInterface $inspection_item_repository, InspectionItemStatusLogRepositoryInterface $inspection_item_status_log_repository)
    {
        $this->issueRepository = $issue_repository;
        $this->inspectionItemStatusLogRepository = $inspection_item_status_log_repository;
        $this->inspectionItemRepository = $inspection_item_repository;
    }

    public function setInspectionItem(InspectionItem $inspection_item)
    {
        $this->inspectionItem = $inspection_item;
        return $this;
    }


    /**
     * @return InspectionItemIssue
     */
    public function create()
    {
        try {
            DB::transaction(function () use (&$issue) {
                $issue = $this->issueRepository->create(['inspection_item_id' => $this->inspectionItem->id]);
                $previous_status = $this->inspectionItem->status;
                $this->inspectionItemRepository->update($this->inspectionItem, ['status' => 'issue_created']);
                $this->inspectionItemStatusLogRepository->create(['inspection_item_id' => $this->inspectionItem->id, 'to_status' => 'issue_created', 'from_status' => $previous_status]);
            });
        } catch (QueryException $e) {
            throw  $e;
        }
        return $issue;
    }
}