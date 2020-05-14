<?php namespace Sheba\Partner\HomePageSettingV3;

use App\Models\Partner;
use Illuminate\Support\Facades\Cache;

class CacheManager
{
    private $redisNameSpace = 'PartnerHomeSetting';
    /** @var Partner $partner */
    private $partner;
    /** @var string $cacheName */
    private $cacheName;
    private $storage;

    public function __construct()
    {
        $this->storage = Cache::store('redis');
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        $this->generateName();

        return $this;
    }

    private function generateName()
    {
        $this->cacheName = sprintf("%s::%d", $this->redisNameSpace, $this->partner->id);
    }

    public function has()
    {
        return $this->storage->has($this->cacheName);
    }

    public function get()
    {
        return $this->storage->get($this->cacheName);
    }

    public function store($data)
    {
        return $this->storage->forever($this->cacheName, $data);
    }
}