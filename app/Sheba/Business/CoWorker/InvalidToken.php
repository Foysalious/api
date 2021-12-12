<?php namespace Sheba\Business\CoWorker;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Sheba\Dal\AuthorizationToken\AuthorizationToken;
use function foo\func;

class InvalidToken
{
    public function invalidTheTokens($email)
    {
        try {
            $valid_authorization_tokens = AuthorizationToken::with('authorizationRequest.profile')->active()
                ->whereHas('authorizationRequest', function ($query) use ($email) {
                    $query->whereHas('profile', function ($query) use ($email) {
                        $query->where('email', $email);
                    });
                })->get();

            foreach ($valid_authorization_tokens as $authorization_token) {
                $authorization_token->update(['is_blacklisted' => 1, 'blacklisted_reason' => 'logout']);
            }
            return true;
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }
}