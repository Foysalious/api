<?php namespace Sheba\Business\EmployeeTracking\Visit;

use Carbon\Carbon;
use Sheba\Dal\Visit\Status;
use Sheba\Dal\Visit\Visit;
use Sheba\Dal\Visit\VisitRepository;
use Sheba\Dal\VisitNote\VisitNoteRepository;
use Sheba\Dal\VisitStatusChangeLog\VisitStatusChangeLogRepo;
use Sheba\Location\Geo;
use Sheba\Map\Client\BarikoiClient;
use DB;

class StatusUpdater
{
    /** @var VisitRepository $visitRepository */
    private $visitRepository;
    /** @var VisitNoteRepository $visitNoteRepository */
    private $visitNoteRepository;
    /** @var VisitStatusChangeLogRepo $statusChangeLogRepo */
    private $statusChangeLogRepo;
    private $visit;
    private $status;
    private $lat;
    private $lng;
    private $note;
    private $date;
    private $geo;
    private $oldStatus;

    public function __construct(VisitRepository $visit_repository,
                                VisitNoteRepository $visit_note_repository, VisitStatusChangeLogRepo $status_change_log_repo)
    {
        $this->visitRepository = $visit_repository;
        $this->visitNoteRepository = $visit_note_repository;
        $this->statusChangeLogRepo = $status_change_log_repo;
    }

    /**
     * @param Visit $visit
     * @return $this
     */
    public function setVisit(Visit $visit)
    {
        $this->visit = $visit;
        $this->oldStatus = $visit->status;
        return $this;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param $lat
     * @return $this
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * @param $lng
     * @return $this
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
        return $this;
    }

    /**
     * @param $note
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date. ' ' . Carbon::now()->format('H:i:s');
        return $this;
    }

    public function update()
    {
        if ($geo = $this->getGeo()) {
            $this->geo = $geo;
            $this->address = $this->getAddress();
        }

        DB::transaction( function () {
            $this->updateVisitStatus();
            $this->createStatusChangeLog();

            if (in_array($this->status, [Status::RESCHEDULED, Status::CANCELLED])) {
                $this->createNote();
            }
        });
    }

    /**
     * @return Geo|null
     */
    private function getGeo()
    {
        if (!$this->lat || !$this->lng) return null;
        $geo = new Geo();
        return $geo->setLat($this->lat)->setLng($this->lng);
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        try {
            return (new BarikoiClient)->getAddressFromGeo($this->geo)->getAddress();
        } catch (\Throwable $exception) {
            return "";
        }
    }

    private function updateVisitStatus()
    {
        $data = [
           'status' => $this->status
        ];
        if ($this->status === Status::STARTED) {
            $data['start_date_time'] = Carbon::now()->format('Y-m-d H:i') . ':00';
        }
        if ($this->status === Status::RESCHEDULED) {
            $data['status'] = Status::CREATED;
            $data['schedule_date'] = $this->date;
            $data['start_date_time'] = null;
        }
        if ($this->status === Status::COMPLETED) {
            $data['end_date_time'] = Carbon::now()->format('Y-m-d H:i') . ':59';
            $data['total_time_in_minutes'] = Carbon::parse($data['end_date_time'])->diffInMinutes($this->visit->start_date_time);
        }

        $this->visitRepository->update($this->visit, $data);
    }

    private function createStatusChangeLog()
    {
        $data = [
            'visit_id' => $this->visit->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->status,
        ];

        if ($this->geo) $data['new_location'] = json_encode(['lat' => $this->geo->getLat(), 'lng' => $this->geo->getLng(), 'address' => $this->address]);

        $this->statusChangeLogRepo->create($data);
    }

    private function createNote() {
        $data = [
            'visit_id' => $this->visit->id,
            'note' => $this->note,
            'status' => $this->status,
            'date' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        $this->visitNoteRepository->create($data);
    }
}