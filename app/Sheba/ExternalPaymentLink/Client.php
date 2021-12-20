<?php namespace Sheba\ExternalPaymentLink;

use Sheba\Dal\PaymentClientAuthentication\Contract as PaymentClientAuthenticationRepo;
use Sheba\ModificationFields;

class Client
{
    use ModificationFields;

    private $name, $details, $whitelisted_ips, $partner_id, $status, $default_purpose, $default_redirect_url, $id;

    private $client_id;

    private $client_secret;

    /**
     * @var PaymentClientAuthenticationRepo
     */
    private $repository;

    const CLIENT_ID_LENGTH = 9;

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    public function setWhitelistedIp($whitelisted_ip)
    {
        $this->whitelisted_ips = $whitelisted_ip;
        return $this;
    }

    public function setClientId()
    {
        $this->client_id = $this->generateID();
        return $this;
    }

    public function setClientSecret()
    {
        $this->client_secret = $this->generateSecret();
        return $this;
    }

    public function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setDefaultFields($request)
    {
        $this->default_purpose      = $request->default_purpose;
        $this->default_redirect_url = $request->default_redirect_url;
        return $this;
    }

    private function generateID()
    {
        return str_pad(mt_rand(1,999999999),self::CLIENT_ID_LENGTH,'0',STR_PAD_LEFT);
    }

    private function generateSecret()
    {
        return str_random(120);
    }

    public function store($modifier = null)
    {
        $this->setModifier($modifier);
        $this->repository->create($this->processData());
    }

    private function processData()
    {
        return [
            "client_id"            => $this->client_id,
            "name"                 => $this->name,
            "client_secret"        => $this->client_secret,
            "details"              => $this->details,
            "whitelisted_ips"      => $this->whitelisted_ips,
            "partner_id"           => $this->partner_id,
            "status"               => $this->status,
            "default_purpose"      => $this->default_purpose,
            "default_redirect_url" => $this->default_redirect_url
        ];
    }

    public function updateSecret($modifier = null)
    {
        $this->setModifier($modifier);
        $this->client()->update($this->withUpdateModificationField([
            "client_secret"   => $this->client_secret
        ]));

        return $this->client();
    }

    public function update($modifier = null)
    {
        $this->setModifier($modifier);
        $this->client()->update($this->withUpdateModificationField([
            "name"                 => $this->name,
            "details"              => $this->details,
            "whitelisted_ips"      => $this->whitelisted_ips,
            "status"               => $this->status,
            "default_purpose"      => $this->default_purpose,
            "default_redirect_url" => $this->default_redirect_url
        ]));

        return $this->client();
    }

    public function client()
    {
        return $this->repository->find($this->id);
    }
}