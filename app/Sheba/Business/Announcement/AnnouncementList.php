<?php namespace Sheba\Business\Announcement;

use App\Models\Business;
use Sheba\Dal\Announcement\Announcement;
use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;

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
        $this->limit = 100;
        $this->offset = 0;
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
        $announcements = Announcement::where('business_id', $this->businessId);
        if ($this->type) $announcements = $announcements->where('type', $this->type);
        $announcements = $announcements->where('status', '<>', 'scheduled')->orWhere('status', null)->orderBy('id', 'desc')
            ->skip($this->offset)
            ->limit($this->limit)
            ->get();
        return $announcements;
    }

}
