<?php namespace Sheba\Business\CoWorker;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvalidToken
{
    public function invalidTheTokens($email)
    {
        try {
            $today = Carbon::now()->format('Y-m-d h:i:s');
            $sub_minute = Carbon::now()->subMinute();

            $authorization_tokens = DB::select("SELECT id FROM authorization_tokens WHERE valid_till >= '$today' AND is_blacklisted = 0 
	   AND authorization_request_id IN ( SELECT id FROM authorization_requests 
	   WHERE profile_id IN ( SELECT id FROM `profiles` WHERE email = '{$email}') ORDER BY id DESC ) ORDER BY id DESC");

            $authorization_tokens_id = collect($authorization_tokens)->pluck('id')->toArray();
            $split_data_authorization_tokens_ids = implode(",", $authorization_tokens_id);

            DB::statement("UPDATE authorization_tokens SET authorization_tokens.valid_till = '$sub_minute', authorization_tokens.refresh_valid_till = '$sub_minute' 
          where authorization_tokens.id  in ($split_data_authorization_tokens_ids)");
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }
}