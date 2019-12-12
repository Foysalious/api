<?php namespace Sheba\Business\Announcement;


use App\Models\Business;
use Carbon\Carbon;
use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;

class Creator
{
    private $announcementRepository;
    private $title;
    private $shortDescription;
    /** @var Carbon */
    private $endDate;
    /** @var Business */
    private $business;

    public function __construct(AnnouncementRepositoryInterface $announcement_repository)
    {
        $this->announcementRepository = $announcement_repository;
    }

    /**
     * @param mixed $title
     * @return Creator
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param mixed $shortDescription
     * @return Creator
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    /**
     * @param Carbon $endDate
     * @return Creator
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @param Business $business
     * @return Creator
     */
    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function create()
    {
        return $this->announcementRepository->create([
            'business_id' => $this->business->id,
            'title' => $this->title,
            'short_description' => $this->shortDescription,
            'end_date' => $this->endDate->toDateTimeString()
        ]);
    }
}