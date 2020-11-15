<?php namespace Sheba\Newsletter;


use Sheba\Dal\Newsletter\NewsletterRepositoryInterface;

class Creator
{
    private $newsletterRepo;

    public $email;
    public $portalName;
    public $ip;
    public $data = [];

    public function __construct(NewsletterRepositoryInterface $newsletter_repository)
    {
        $this->newsletterRepo = $newsletter_repository;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setPortalName($portal_name)
    {
        $this->portalName = $portal_name;
        return $this;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    public function store()
    {
        $this->formatData();
        $this->newsletterRepo->create($this->data);
    }

    private function formatData()
    {
        $this->data = [
            'email' => $this->email,
            'portal_name' => $this->portalName,
            'ip' => $this->ip,
        ];
    }
}