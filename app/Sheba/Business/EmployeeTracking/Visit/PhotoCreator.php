<?php namespace Sheba\Business\EmployeeTracking\Visit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Image;
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
        if ($this->isFile($this->photo)) {
            $name = $this->getClientOriginalNameWithoutExtension();
            $photo = $this->saveVisitPhoto($this->photo, $name);

            $this->makeData($photo);
            DB::transaction(function () {
                $this->visitPhotoRepository->create($this->visitPhotoData);
            });
        }
    }

    /**
     * @return array|string|string[]
     */
    private function getClientOriginalNameWithoutExtension()
    {
        return pathinfo($this->photo->getClientOriginalName(), PATHINFO_FILENAME);
    }

    /**
     * @param $image
     * @return bool
     */
    private function isFile($image)
    {
        if ($image instanceof Image || $image instanceof UploadedFile) return true;
        return false;
    }

    /**
     * @param $photo
     * @param $name
     * @return string
     */
    private function saveVisitPhoto($photo, $name)
    {
        list($photo, $visit_filename) = $this->makeAttachment($photo, $name);
        return $this->saveFileToCDN($photo, getEmployeeVisitFolder(), $visit_filename);
    }

    private function makeData($photo)
    {
        $this->visitPhotoData = [
            'visit_id' => $this->visit->id,
            'photo' => $photo
        ];
    }
}