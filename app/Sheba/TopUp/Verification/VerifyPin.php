<?php namespace Sheba\TopUp\Verification;

use App\Exceptions\ApiValidationException;
use App\Models\Affiliate;
use App\Models\Partner;
use ReflectionClass;
use ReflectionException;
use Sheba\Dal\AuthenticationRequest\Purpose;
use Sheba\Dal\AuthenticationRequest\Statuses;
use Sheba\Dal\WrongPINCount\Contract as WrongPINCountRepo;
use Sheba\ModificationFields;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\AuthUser;
use Sheba\TopUp\Exception\PinMismatchException;
use Sheba\TopUp\Exception\ResetRememberTokenException;

class VerifyPin
{
    use ModificationFields;

    const WRONG_PIN_COUNT_LIMIT = 3;
    private $agent;
    private $profile;
    private $request;
    private $managerResource;
    /** @var AuthUser */
    private $authUser;
    /**
     * @var WrongPINCountRepo $wrongPinCountRepo
     */
    private $wrongPinCountRepo;
    /** @var AccountServer */
    private $accountServer;

    public function __construct(WrongPINCountRepo $wrongPinCountRepo, AccountServer $accountServer)
    {
        $this->wrongPinCountRepo = $wrongPinCountRepo;
        $this->accountServer = $accountServer;
    }

    /**
     * @param mixed $profile
     * @return VerifyPin
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * @param mixed $request
     * @return VerifyPin
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param mixed $agent
     * @return VerifyPin
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * @param AuthUser $authUser
     * @return VerifyPin
     */
    public function setAuthUser($authUser)
    {
        $this->authUser = $authUser;
        return $this;
    }

    private function wrongPinQuery()
    {
        return $this->wrongPinCountRepo->where('type_id', $this->agent->id)->where('type', class_basename($this->agent));
    }


    public function verify()
    {
        $result = $this->accountServer->passwordAuthenticate($this->profile->mobile, $this->request->password, Purpose::TOPUP);
        $data = json_decode($result->getBody(), true);
        if ($data['code'] == 200) return;
        if ($data['code'] == 500) throw new ApiValidationException("Something went wrong.", 500);
        if (count($data['log_attempts']) < self::WRONG_PIN_COUNT_LIMIT) throw new PinMismatchException();
        for ($i = 0; $i < self::WRONG_PIN_COUNT_LIMIT; $i++) {
            if ($data['log_attempts'][$i]['status'] != Statuses::FAIL) {
                throw new PinMismatchException();
            }
        }
        $this->logout();
        $this->resetRememberToken();
        throw new ApiValidationException("You have been logged out", 403);
    }

    /**
     * @param mixed $managerResource
     * @return VerifyPin
     */
    public function setManagerResource($managerResource)
    {
        $this->managerResource = $managerResource;
        return $this;
    }

    private function resetRememberToken()
    {
        if ($this->managerResource && $this->agent instanceof Partner) $this->managerResource->update($this->withUpdateModificationField(['remember_token' => str_random(255)]));
        if ($this->agent instanceof Affiliate) $this->agent->update(['remember_token' => str_random(255)]);
    }

    /**
     * @throws ReflectionException
     */
    private function getType()
    {
        $class = (new ReflectionClass($this->agent))->getShortName();
        return strtolower($class);
    }

    private function logout()
    {
        if (!$this->authUser) return;
        $this->accountServer->logout($this->request->access_token->token);
    }
}
