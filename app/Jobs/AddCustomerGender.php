<?php namespace App\Jobs;

use App\Models\Profile;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class AddCustomerGender extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $profile;
    private $now;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
        $this->now = Carbon::now();
    }

    public function handle()
    {
        if ($this->attempts() <= 1 && !$this->isLimitOverForToday()) {
            $gender = $this->getGender();
            if ($gender) $this->addGender($gender);
        }
    }

    private function addGender($gender)
    {
        $this->profile->gender = ucfirst($gender);
        $this->profile->update();
    }

    private function getGender()
    {
        try {
            $client = new Client();
            $response = $client->request('GET', 'https://genderapi.io/api', ['query' => ['name' => $this->profile->name, 'key' => config('research.genderapi_key')]]);
            return $this->extractGenderFromResponse(json_decode($response->getBody()));
        } catch (RequestException $exception) {
            return null;
        }
    }

    private function extractGenderFromResponse($response)
    {
        if (!$response) return null;
        if (isset($response->errno) && (int)$response->errno == 93) {
            $gender_api = json_decode($this->getFromRedis());
            $data = ['is_expired' => 1];
            if (isset($gender_api->expires)) $data['renews'] = (int)$gender_api->expires;
            $this->setToRedis(json_encode($data));
        }
        if (isset($response->status) && $response->status) {
            $this->setToRedis(json_encode(['is_expired' => 0, 'renews' => (int)$response->expires]));
            if ((int)$response->probability > 95) return $response->gender;
        };
        return null;
    }

    private function isLimitOverForToday()
    {
        if ($gender_api = $this->getFromRedis()) {
            $gender_api = json_decode($gender_api);
            return isset($gender_api->is_expired) && $gender_api->is_expired && isset($gender_api->renews) && $this->now->timestamp < (int)$gender_api->renews;
        } else
            return false;
    }

    private function getFromRedis()
    {
        return Redis::get('genderapi');
    }

    private function setToRedis($data)
    {
        Redis::set('genderapi', $data);
    }
}