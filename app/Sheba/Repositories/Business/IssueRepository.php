<?php namespace Sheba\Repositories\Business;


use App\Models\InspectionItemIssue;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\IssueRepositoryInterface;

class IssueRepository extends BaseRepository implements IssueRepositoryInterface
{
    public function __construct(InspectionItemIssue $issue)
    {
        parent::__construct();
        $this->setModel($issue);
    }
}