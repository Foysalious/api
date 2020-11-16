<?php


namespace Sheba\TopUp\Verification;


use Illuminate\Support\Facades\Hash;
use ReflectionException;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\Dal\WrongPINCount\Contract as WrongPINCountRepo;
use Sheba\ModificationFields;
use Sheba\TopUp\Commission\Partner;
use Sheba\TopUp\Exception\PinMismatchException;
use Sheba\TopUp\Exception\ResetRememberTokenException;

class VerifyPin
{
    use ModificationFields;
    const WRONG_PIN_COUNT_LIMIT=3;
    private $agent;
    private $profile;
    private $request;
    private $managerResource;
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
                'profile_id' => $this->profile,
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
        if ($this->agent instanceof Partner) $this->managerResource->update($this->withUpdateModificationField(['remember_token' => str_random(255)]));
        $this->agent->update($this->withUpdateModificationField(['remember_token' => str_random(255)]));
    }

    /**
     * @throws ReflectionException
     */
    private function getType()
    {
        $class=(new \ReflectionClass($this->agent))->getShortName();;
        return strtolower($class);
    }
}