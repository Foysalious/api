<?php namespace Sheba\Business\Announcement;


use App\Models\Business;
use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;
use Sheba\Dal\Announcement\AnnouncementTypes;

class AnnouncementList
{
    private $limit;
    private $offset;
    private $type;
    /** @var Business */
    private $business;
    private $businessId;
    private $announcementRepository;

    public function __construct(AnnouncementRepositoryInterface $announcement_repository)
    {
        $this->limit = 0;
        $this->offset = 100;
        $this->announcementRepository = $announcement_repository;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param int $limit
     * @return AnnouncementList
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return AnnouncementList
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param mixed $businessId
     * @return AnnouncementList
     */
    public function setBusinessId($businessId)
    {
        $this->businessId = $businessId;
        return $this;
    }

    public function get()
    {
        $this->announcementRepository = $this->announcementRepository->where('business_id', $this->businessId);
        if ($this->type) $this->announcementRepository = $this->announcementRepository->where('type', $this->type);
        return $this->announcementRepository->select('id', 'title', 'short_description', 'end_date', 'created_at')->orderBy('id', 'desc')
            ->skip($this->offset)
            ->limit($this->limit)
            ->get();
    }

}