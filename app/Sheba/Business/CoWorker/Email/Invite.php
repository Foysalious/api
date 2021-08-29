<?php namespace Sheba\Business\CoWorker\Email;

use Sheba\Repositories\ProfileRepository;
use App\Jobs\SendBusinessRequestEmail;
use App\Models\Profile;
use Throwable;

class Invite
{
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /**  @var Profile $profile */
    private $profile;
    private $password;

    public function __construct(Profile $profile)
    {
        $this->profileRepository = app(ProfileRepository::class);
        $this->profile = $profile;
    }

    public function sendReInviteMail()
    {
        try {
            config()->set('services.mailgun.domain', config('services.mailgun.business_domain'));
            $coworker_invite_email = new SendBusinessRequestEmail($this->profile->email);
            $coworker_invite_email->setPassword($this->updatePassword());
            $coworker_invite_email->setSubject("Invitation from your co-worker to join digiGO")->setTemplate('emails.co-worker-invitation-v3');
            dispatch($coworker_invite_email);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
        }
    }

    public function sendMailToAddUser()
    {
        try {
            config()->set('services.mailgun.domain', config('services.mailgun.business_domain'));
            $coworker_invite_email = new SendBusinessRequestEmail($this->profile->email);
            $coworker_invite_email->setPassword($this->updatePassword());
            $coworker_invite_email->setSubject("Invitation from your co-worker to join digiGO")->setTemplate('emails.co-worker-invitation-v3');
            dispatch($coworker_invite_email);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
        }
    }

    /**
     * @return string
     */
    private function updatePassword()
    {
        $this->password = $password = str_random(6);
        $this->profileRepository->updateRaw($this->profile, ['password' => bcrypt($password)]);
        return $this->password;
    }
}