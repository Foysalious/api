<?php namespace Sheba\AppVersion;

use Illuminate\Support\Facades\Redis;
use Intervention\Image\Image;
use Sheba\Dal\AppVersion\AppVersionRepository;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;

class AppVersionManager
{
    use FileManager, CdnFileManager;

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
    public function getVersionForApp($app, $version): AppVersionDTO
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
    public function hasCriticalUpdate($app, $version): bool
    {
        return $this->repo->hasCriticalUpdate($app, $version);
    }

    /**
     * @param App $app
     * @param $version_name
     * @param $data
     * @return mixed
     */
    public function createNewVersion(App $app, $version_name, $data)
    {
        $create_data = [
            'name'          => $app->getMarketName(),
            'tag'           => $app->getName(),
            'package_name'  => $app->getPackageName(),
            'platform'      => $app->getPlatformName(),
            'title'         => $data['title'],
            'body'          => $data['body'],
            'version_name'  => $version_name,
            'version_code'  => $this->convertSemverToInt($version_name),
            'is_critical'   => $data['is_critical'] ? 1 : 0
        ];

        if (!empty($data['image'])) {
            /** @var Image $image */
            $image = $data['image'];
            $create_data['image_link'] = $this->saveImages($app, $image);
            $create_data['width'] = $image->getWidth();
            $create_data['height'] = $image->getHeight();
        }

        return $this->repo->create($create_data);
    }

    public function getAllAppVersions()
    {
        $apps = json_decode(Redis::get('app_versions'));
        $apps = $apps ?: $this->scrapeAppVersionsAndStoreInRedis();
        return $apps;
    }

    /**
     * @param string $semver
     * @return int
     */
    public function convertSemverToInt($semver): int
    {
        return (int)str_replace('.', '', $semver);
    }

    /**
     * @param int $version_code
     * @return string
     */
    public function convertIntToSemver($version_code): string
    {
        return implode(".", explode("", "" . $version_code));
    }

    private function saveImages(App $app, Image $file): string
    {
        list($image, $filename) = $this->makeAppVersionImage($file, $app->getName());
        return $this->saveImageToCDN($image, getAppVersionImageLinkFolder(), $filename);
    }

    public function scrapeAppVersionsAndStoreInRedis(): array
    {
        $version_string = 'itemprop="softwareVersion">';
        $apps           = Apps::getPackageNames();
        $final          = [];
        foreach ($apps as $key => $value) {
            $value = "https://play.google.com/store/apps/details?id=" . $value;
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
