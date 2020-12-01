<?php namespace Sheba\Profile\Listeners;


use Sheba\Dal\Profile\Events\ProfilePasswordUpdated;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;


class ProfilePasswordUpdatedListener
{
    /** @var ProfileRepositoryInterface */
    private $profileRepository;

    public function __construct(ProfileRepositoryInterface $profile_repository)
    {
        $this->profileRepository = $profile_repository;
    }

    public function handle(ProfilePasswordUpdated $event)
    {
        $this->profileRepository->update($event->model, ['login_blocked_until' => null]);
    }
}