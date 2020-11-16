<?php namespace Sheba\CancelRequest;


use App\Models\Job;
use App\Models\Resource;
use App\Models\User;

class SendCancelRequest
{
    private $jobId;
    /** @var User|Resource */
    private $requestedBy;
    private $requestedById;
    private $requestedByType;
    /** @var bool */
    private $isEscalated;
    /** @var string */
    private $cancelReason;
    /** @var string */
    private $userAgent;
    /** @var string */
    private $ip;
    /** @var string */
    private $portalName;


    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
        return $this;
    }

    public function setRequestedById($requestedById)
    {
        $this->requestedById = $requestedById;
        return $this;
    }

    public function setRequestedByType($requestedByType)
    {
        $this->requestedByType = $requestedByType;
        return $this;
    }

    public function setIsEscalated($isEscalated)
    {
        $this->isEscalated = $isEscalated;
        return $this;
    }

    public function setCancelReason($cancelReason)
    {
        $this->cancelReason = $cancelReason;
        return $this;
    }

    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    public function setPortalName($portalName)
    {
        $this->portalName = $portalName;
        return $this;
    }

    /**
     * @return Job
     */
    public function getJob()
    {
        return Job::find($this->jobId);
    }

    /*
     * @return string
     */
    public function getCancelReason()
    {
        return $this->cancelReason;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getPortalName()
    {
        return $this->portalName;
    }

    /**
     * @return mixed
     */
    public function getRequestedByType()
    {
        return $this->requestedByType;
    }

    /**
     * @return bool
     */
    public function getIsEscalated()
    {
        return $this->isEscalated;
    }

    /**
     * @return mixed
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * @return mixed
     */
    public function getRequestedById()
    {
        return $this->requestedById;
    }

    public function getRequester()
    {
        if (!$this->requestedBy) $this->setRequestedBy($this->getRequestedByType() == RequestedByType::USER ? User::find($this->requestedById) : Resource::find($this->requestedById));
        return $this->requestedBy;
    }

    private function setRequestedBy($requestedBy)
    {
        $this->requestedBy = $requestedBy;
        return $this;
    }

    public function getRequesterName()
    {
        $requester = $this->getRequester();
        return $requester instanceof User ? $requester->name : $requester->profile->name;
    }

}