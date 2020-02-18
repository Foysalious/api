<?php namespace Sheba\Schema;


use App\Models\Category;
use App\Models\Service;
use Illuminate\Contracts\Cache\Repository;
use Cache;

class ServiceSchema
{
    private $service;
    private $serviceId;
    private $redisNameSpace;
    /** @var Repository $store */
    private $store;
    const BREADCRUMB_SCHEMA_NAME = 'breadcrumb';
    const FAQ_SCHEMA_NAME = 'faq';

    public function __construct()
    {
        $this->redisNameSpace = 'schema';
        $this->store = Cache::store('redis');
    }

    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    private function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    public function get()
    {
        $cache_name = sprintf("%s::%s_%d", $this->redisNameSpace, 'service', $this->serviceId);
        $data = $this->store->get($cache_name);
        if ($data) return json_decode($data, true);
        else return $this->generate();
    }

    private function generate()
    {
        $this->setService(Service::find($this->serviceId));
        $data = [
            self::FAQ_SCHEMA_NAME => $this->getFaqSchema(),
            self::BREADCRUMB_SCHEMA_NAME => $this->getBreadcrumb(),
        ];
        $cache_name = sprintf("%s::%s_%d", $this->redisNameSpace, 'service', $this->serviceId);
        $this->store->forever($cache_name, json_encode($data));
        return $data;
    }

    private function getFaqSchema()
    {
        $faqs = $this->service->faqs ? json_decode($this->service->faqs, true) : [];
        $lists = [];
        foreach ($faqs as $key => $faq) {
            array_push($lists, [
                "@type" => "Question",
                "name" => $faq['question'],
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => $faq['answer']
                ]
            ]);
        }
        return [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $lists
        ];
    }

    private function getBreadcrumb()
    {
        $marketplace_url = config('sheba.front_url');
        $items = [
            [
                'name' => 'Sheba Platform Limited',
                'url' => $marketplace_url
            ]
        ];
        $category = Category::select('id', 'name', 'slug', 'parent_id')->where('id', $this->service->category_id)->first();
        $master = Category::select('id', 'name', 'slug')->where('id', $category->parent_id)->first();


        array_push($items, [
            'name' => $master->name,
            'url' => $marketplace_url . '/' . $master->slug,
        ], [
            'name' => $category->name,
            'url' => $marketplace_url . '/' . $category->slug,
        ], [
            'name' => $this->service->name,
            'url' => $marketplace_url . '/' . $this->service->slug,
        ]);

        $itemListElement = [];
        foreach ($items as $key => $value) {
            array_push($itemListElement, [
                "@type" => "ListItem",
                "position" => (int)$key + 1,
                "name" => $value['name'],
                "item" => $value['url']
            ]);
        }

        return [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => $itemListElement
        ];
    }
}