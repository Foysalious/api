<?php namespace Sheba\AppVersion;


use Illuminate\Support\Facades\Redis;
use Sheba\Dal\AppVersion\AppVersionRepository;

class AppVersionManager
{
    /** @var AppVersionRepository */
    private $repo;

    public function __construct(AppVersionRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param $app
     * @param $version
     * @return AppVersionDTO
     */
    public function getVersionForApp($app, $version)
    {
        $versions = $this->repo->getByAppAndVersion($app, $version);

        $data = new AppVersionDTO();
        if (!$versions->isEmpty()) $data->setVersion($versions->last());
        $data->setHasCritical(count($versions->where('is_critical', 1)) > 0);
        return $data;
    }

    /**
     * @param $app
     * @param $version
     * @return bool
     */
    public function hasCriticalUpdate($app, $version)
    {
        return $this->repo->hasCriticalUpdate($app, $version);
    }

    public function getAllAppVersions()
    {
        $apps = json_decode(Redis::get('app_versions'));
        $apps = $apps ?: $this->scrapeAppVersionsAndStoreInRedis();
        return $apps;
    }

    private function scrapeAppVersionsAndStoreInRedis()
    {
        $version_string = 'itemprop="softwareVersion">';
        $apps           = constants('APPS');
        $final          = [];
        foreach ($apps as $key => $value) {
            $headers      = get_headers($value);
            $version_code = 0;
            if (substr($headers[0], 9, 3) == "200") {
                $dom           = file_get_contents($value);
                $version       = strpos($dom, $version_string);
                $result_string = trim(substr($dom, $version + strlen($version_string), 15));
                $final_string  = explode(' ', $result_string);
                $version_code  = (int)str_replace('.', '', $final_string[0]);
            }
            array_push($final, ['name' => $key, 'version_code' => $version_code, 'is_critical' => 0]);
        }
        Redis::set('app_versions', json_encode($final));
        return $final;
    }
}