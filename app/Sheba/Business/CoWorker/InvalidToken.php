<?php namespace Sheba\Business\CoWorker;

use Sheba\Dal\AuthorizationToken\AuthorizationTokenRepositoryInterface;

class InvalidToken
{
    /** @var AuthorizationTokenRepositoryInterface $authorizeTokenRepo */
    private $authorizeTokenRepo;

    public function __construct()
    {
        $this->authorizeTokenRepo = app(AuthorizationTokenRepositoryInterface::class);
    }

    public function invalidTheTokens($email = 'b2btest@sheba.xyz')
    {
        $invalid_tokens = $this->authorizeTokenRepo->builder()->
            dd($invalid_tokens);
    }
}