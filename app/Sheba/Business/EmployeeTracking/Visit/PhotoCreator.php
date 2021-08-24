<?php namespace Sheba\Business\EmployeeTracking\Visit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Visit\Visit;
use Sheba\Dal\VisitPhoto\VisitPhotoRepository;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;

class PhotoCreator
{
    use CdnFileManager, FileManager;

    /** @var VisitPhotoRepository $visitPhotoRepository */
    private $visitPhotoRepository;
    private $photo;
    private $visitPhotoData = [];

    /**
     * @param VisitPhotoRepository $visit_photo_repository
     */
    public function __construct(VisitPhotoRepository $visit_photo_repository)
    {
       $this->visitPhotoRepository = $visit_photo_repository;
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
     * @param $photo
     * @return $this
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
        return $this;
    }

    public function store()
    {
        if ($this->photo instanceof UploadedFile) {
            $name = 'visit_photo'.' '.rand(100,100000);
            $photo = $this->saveVisitPhoto($this->photo, $name);

            $this->makeData($photo);
            DB::transaction(function () {
                $this->visitPhotoRepository->create($this->visitPhotoData);
            });
        }
    }

    /**
     * @param $photo
     * @param $name
     * @return string
     */
    private function saveVisitPhoto($photo, $name)
    {
        list($photo, $visit_filename) = $this->makeAttachment($photo, $name);
        return $this->saveImageToCDN($photo, getEmployeeVisitFolder(), $visit_filename);
    }

    private function makeData($photo)
    {
        $this->visitPhotoData = [
            'visit_id' => $this->visit->id,
            'photo' => $photo
        ];
    }
}