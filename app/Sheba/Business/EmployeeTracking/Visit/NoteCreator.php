<?php namespace Sheba\Business\EmployeeTracking\Visit;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Visit\Visit;
use Sheba\Dal\VisitNote\VisitNoteRepository;

class NoteCreator
{
    /** @var VisitNoteRepository $visitNoteRepository */
    private $visitNoteRepository;
    private $visit;
    private $date;
    private $note;
    private $status;

    /**
     * @param VisitNoteRepository $visit_note_repository
     */
    public function __construct(VisitNoteRepository $visit_note_repository)
    {
        $this->visitNoteRepository = $visit_note_repository;
    }

    /**
     * @param Visit $visit
     * @return $this
     */
    public function setVisit(Visit $visit)
    {
        $this->visit = $visit;
        return $this;
    }

    /**
     * @param $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date . ' ' . Carbon::now()->format('H:i:s');
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
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function store()
    {
        DB::transaction(function () {
            $this->visitNoteRepository->create($this->makeData());
        });
    }

    private function makeData()
    {
        return [
            'visit_id' => $this->visit->id,
            'note' => $this->note,
            'status' => $this->status,
            'date' => $this->date,
        ];
    }
}