<?php namespace Sheba\OAuth2;

use App\Exceptions\DoNotReportException;
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
    private $purpose;

    /**
     * VerifyPin constructor.
     * @param AccountServer $accountServer
     */
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

    /**
     * @param $purpose
     * @return VerifyPin
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
        return $this;
    }

    /**
     * @return string
     */
    private function logoutReason()
    {
        if ($this->purpose == Purpose::TOPUP) return BlacklistedReason::TOPUP_LOGOUT;
        if ($this->purpose == Purpose::PAYSLIP_DISBURSE) return BlacklistedReason::PAYSLIP_LOGOUT;
    }

    /**
     * @throws DoNotReportException
     * @throws PinMismatchException
     * @throws \Sheba\OAuth2\AccountServerAuthenticationError
     * @throws \Sheba\OAuth2\AccountServerNotWorking
     * @throws \Sheba\OAuth2\WrongPinError
     */
    public function verify()
    {
        $this->authenticateWithPassword();
    }

    /**
     * @throws \Exception
     * @throws DoNotReportException
     * @throws PinMismatchException
     * @throws \Sheba\OAuth2\AccountServerAuthenticationError
     * @throws \Sheba\OAuth2\AccountServerNotWorking
     * @throws \Sheba\OAuth2\WrongPinError
     */
    private function authenticateWithPassword()
    {
        try {
            $this->accountServer->passwordAuthenticate($this->profile->mobile, $this->profile->email, $this->request->password, $this->purpose);
        } catch (\Exception $e) {
            if ($e->getCode() != 403) throw $e;
            $this->getAuthenticateRequests();
        }
    }

    /**
     * @throws DoNotReportException
     * @throws PinMismatchException
     * @throws \Sheba\OAuth2\AccountServerAuthenticationError
     * @throws \Sheba\OAuth2\AccountServerNotWorking
     * @throws \Sheba\OAuth2\WrongPinError
     */
    private function getAuthenticateRequests()
    {
        $data = $this->accountServer->getAuthenticateRequests($this->request->access_token->token, $this->purpose);
        $continuous_wrong_pin_attempted = $this->getConsecutiveFailedCount($data['requests']);

        if (count($data['requests']) < self::WRONG_PIN_COUNT_LIMIT)
            throw new PinMismatchException($continuous_wrong_pin_attempted, $message = "Pin Mismatch", $code = 403);

        for ($i = 0; $i < self::WRONG_PIN_COUNT_LIMIT; $i++) {
            if ($data['requests'][$i]['status'] != Statuses::FAIL) {
                throw new PinMismatchException($continuous_wrong_pin_attempted, $message = "Pin Mismatch", $code = 403);
            }
        }

        $this->sessionOut();
    }

    /**
     * @param $request_status
     * @return int
     */
    private function getConsecutiveFailedCount($request_status)
    {
        foreach ($request_status as $i => $data) {
            if ($data['status'] == Statuses::SUCCESS) return (int)$i;
        }
        return count($request_status);
    }

    /**
     * @throws DoNotReportException
     * @throws \Sheba\OAuth2\AccountServerAuthenticationError
     * @throws \Sheba\OAuth2\AccountServerNotWorking
     * @throws \Sheba\OAuth2\WrongPinError
     */
    private function sessionOut()
    {
        $this->logout();
        $this->resetRememberToken();
        throw new DoNotReportException("You have been logged out", 401);
    }

    /**
     * @throws \Sheba\OAuth2\AccountServerAuthenticationError
     * @throws \Sheba\OAuth2\AccountServerNotWorking
     * @throws \Sheba\OAuth2\WrongPinError
     */
    private function logout()
    {
        $this->accountServer->logout($this->request->access_token->token, $this->logoutReason());
    }

    private function resetRememberToken()
    {
        if ($this->managerResource && $this->agent instanceof Partner) $this->managerResource->update($this->withUpdateModificationField(['remember_token' => str_random(255)]));
        if ($this->agent instanceof Affiliate) $this->agent->update(['remember_token' => str_random(255)]);
    }
}
