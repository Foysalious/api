<?php namespace Sheba\Business\Driver;

use App\Repositories\FileRepository;
use Sheba\Repositories\Interfaces\DriverRepositoryInterface;
use Sheba\Repositories\ProfileRepository;

class Creator
{
    /** @var CreateRequest $driverCreateRequest */
    private $driverCreateRequest;
    /** @var FileRepository $fileRepository */
    private $fileRepository;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;

    public function __construct(FileRepository $file_repository, ProfileRepository $profile_repository,
                                DriverRepositoryInterface $driver_repo)
    {
        $this->fileRepository = $file_repository;
        $this->profileRepository = $profile_repository;
    }

    /**
     * @param CreateRequest $create_request
     * @return $this
     */
    public function setDriverCreateRequest(CreateRequest $create_request)
    {
        $this->driverCreateRequest = $create_request;
        return $this;
    }

    public function create()
    {
        $is_profile_exist = $this->profileRepository->checkExistingMobile($this->driverCreateRequest->getDriverMobile());
        return true;
    }

    private function formatDriverSpecificData()
    {
        return [
            'status' => 'active',
            'license_number' => $this->driverCreateRequest->getLicenseNumber(),
            'license_class' => $this->driverCreateRequest->getLicenseClass()
        ];
    }
}