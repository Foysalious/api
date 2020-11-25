<?php


namespace Sheba\TopUp\Verification;


use App\Models\Affiliate;
use App\Models\Partner;
use App\Models\Resource;
use Illuminate\Support\Facades\Hash;
use ReflectionClass;
use ReflectionException;
use Sheba\Helpers\Formatters\BDMobileFormatter;
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

    public function __construct()
    {
        $this->wrongPinCountRepo = app(WrongPINCountRepo::class);
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

    /**
     * @throws PinMismatchException
     * @throws ResetRememberTokenException|ReflectionException
     */
    public function verify()
    {
        if (!Hash::check($this->request->password, $this->profile->password)) {

            $data = [
                'type_id' => $this->agent->id,
                'type' => $this->getType(),
                'topup_number' => BDMobileFormatter::format($this->request->mobile),
                'topup_amount' => $this->request->amount,
                'password' => $this->request->password,
                'ip_address' => $this->request->ip(),
            ];
            $this->wrongPinCountRepo->create($this->withBothModificationFields($data));
            $wrongPinCount = $this->wrongPinQuery()->get()->count();

            if ($wrongPinCount >= self::WRONG_PIN_COUNT_LIMIT) {
                $this->logout();
                $this->resetRememberToken();
                $this->wrongPinQuery()->delete();
                throw new ResetRememberTokenException();
            }

            throw new PinMismatchException();

        } else {
            $wp_count = $this->wrongPinQuery()->get()->count();
            if ($wp_count > 0) {
                $this->wrongPinQuery()->delete();
            }
        }

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
        if ($this->agent instanceof Affiliate) $this->agent->update($this->withUpdateModificationField(['remember_token' => str_random(255)]));
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
        /** @var AccountServer $account_server */
        $account_server = app(AccountServer::class);
        $account_server->logout($this->request->access_token->token);
    }
}