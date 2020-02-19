<?php namespace Sheba\Schema;


use App\Models\Category;
use App\Models\Service;

class ServiceSchema
{
    private $service;
    const BREADCRUMB_SCHEMA_NAME = 'breadcrumb';
    const FAQ_SCHEMA_NAME = 'faq';

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    public function get()
    {
        return [
            self::FAQ_SCHEMA_NAME => $this->getFaqSchema(),
            self::BREADCRUMB_SCHEMA_NAME => $this->getBreadcrumb(),
        ];
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
        $items = [['name' => 'Sheba Platform Limited', 'url' => $marketplace_url]];
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