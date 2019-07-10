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
    private $keys;
    private $apiKey;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
        $this->now = Carbon::now();
        $this->keys = ['5c835974615dc558d6147d82', '5d0b6893e4b204231e617b82', '5d1adb47e4b20453bd07e3f2'];
    }

    public function handle()
    {
        if (config('app.env') == 'production' && empty($this->profile->gender) && $this->attempts() <= 1) {
            foreach ($this->keys as $key) {
                if (!$this->isLimitOverForToday($key)) {
                    $this->setApiKey($key);
                    if ($gender = $this->getGender()) {
                        $this->addGender($gender);
                        break;
                    }
                }
            }
        }
    }

    private function isLimitOverForToday($key)
    {
        if ($gender_api = $this->getFromRedis()) {
            $redis_key = collect(json_decode($gender_api))->where('key', $key)->first();
            return !$redis_key || !$this->isKeyExpired($redis_key) ? 0 : 1;
        } else {
            return 0;
        }
    }

    private function getFromRedis()
    {
        return Redis::get('genderapi');
    }

    /**
     * @param $redis_key
     * @return bool
     */
    private function isKeyExpired($redis_key)
    {
        return Carbon::createFromTimestamp($redis_key->expired_at)->isToday();
    }

    private function setApiKey($api_key)
    {
        $this->apiKey = $api_key;
        return $this;
    }

    private function getGender()
    {
        try {
            $client = new Client();
            $response = $client->request('GET', 'https://genderapi.io/api', ['query' => ['name' => $this->profile->name, 'key' => $this->apiKey]]);
            return $this->extractGenderFromResponse(json_decode($response->getBody()));
        } catch (RequestException $exception) {
            return null;
        }
    }

    private function extractGenderFromResponse($response)
    {
        if (!$response) return null;
        if (isset($response->errno) && (int)$response->errno == 93) {
            $gender_api = $this->getFromRedis();
            $data = ['key' => $this->apiKey, 'expired_at' => $this->now->timestamp];
            if ($gender_api) {
                $gender_api = collect(json_decode($this->getFromRedis()))->reject(function ($key) {
                    return $key->key == $this->apiKey;
                })->values()->all();
                array_push($gender_api, $data);
                $this->setToRedis(json_encode($gender_api));
            } else {
                $this->setToRedis(json_encode([$data]));
            }
        }
        if (isset($response->status) && $response->status && (int)$response->probability >= 90) {
            return $response->gender;
        };
        return null;
    }

    private function setToRedis($data)
    {
        Redis::set('genderapi', $data);
        Redis::expire('genderapi', 60 * 60);
    }

    private function addGender($gender)
    {
        $this->profile->gender = ucfirst($gender);
        $this->profile->update();
    }


}