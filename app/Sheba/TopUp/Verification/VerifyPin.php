<?php namespace Sheba\TopUp\Verification;

use App\Exceptions\ApiValidationException;
use App\Models\Affiliate;
use App\Models\Partner;
use GuzzleHttp\Exception\ClientException;
use Sheba\Dal\AuthenticationRequest\Purpose;
use Sheba\Dal\AuthenticationRequest\Statuses;
use Sheba\Dal\AuthorizationToken\BlacklistedReason;
use Sheba\ModificationFields;
use Sheba\OAuth2\AccountServer;
use Sheba\TopUp\Exception\PinMismatchException;

class VerifyPin
{
    use ModificationFields;

    const WRONG_PIN_COUNT_LIMIT = 3;
    private $agent;
    private $profile;
    private $request;
    private $managerResource;
    /** @var AccountServer */
    private $accountServer;

    public function __construct(AccountServer $accountServer)
    {
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

    public function verify()
    {
        $this->authenticateWithPassword();
    }

    private function authenticateWithPassword()
    {
        try {
            $this->accountServer->passwordAuthenticate($this->profile->mobile, $this->request->password, Purpose::TOPUP);
        } catch (ClientException $e) {
            if ($e->getCode() != 403) throw new ApiValidationException("Something went wrong", 500);
            $this->getAuthenticateRequests();
        }
    }

    /**
     * @throws ApiValidationException
     * @throws PinMismatchException
     */
    private function getAuthenticateRequests()
    {
        $result = $this->accountServer->getAuthenticateRequests($this->request->access_token->token, Purpose::TOPUP);
        $data = json_decode($result->getBody(), true);
        if (count($data['requests']) < self::WRONG_PIN_COUNT_LIMIT) throw new PinMismatchException();
        for ($i = 0; $i < self::WRONG_PIN_COUNT_LIMIT; $i++) {
            if ($data['requests'][$i]['status'] != Statuses::FAIL) {
                throw new PinMismatchException();
            }
        }
        $this->sessionOut();
    }

    private function sessionOut()
    {
        $this->logout();
        $this->resetRememberToken();
        throw new ApiValidationException("You have been logged out", 401);
    }

    private function logout()
    {
        $this->accountServer->logout($this->request->access_token->token, BlacklistedReason::TOPUP_LOGOUT);
    }

    private function resetRememberToken()
    {
        if ($this->managerResource && $this->agent instanceof Partner) $this->managerResource->update($this->withUpdateModificationField(['remember_token' => str_random(255)]));
        if ($this->agent instanceof Affiliate) $this->agent->update(['remember_token' => str_random(255)]);
    }


}
