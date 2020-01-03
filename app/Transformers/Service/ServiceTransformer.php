<?php namespace App\Transformers\Service;


use App\Models\Service;
use App\Sheba\Service\ServiceQuestion;
use League\Fractal\TransformerAbstract;
use DB;

class ServiceTransformer extends TransformerAbstract
{
    private $serviceQuestion;

    public function __construct(ServiceQuestion $service_question)
    {
        $this->serviceQuestion = $service_question;
    }


    public function transform(Service $service)
    {
        $usps = $service->category->usps()->select('name')->get();
        $partnership = $service->partnership;
        $galleries = $service->galleries()->select('id', DB::Raw('thumb as image'))->get();
        $blog_posts = $service->blogPosts()->select('id', 'title', 'short_description', DB::Raw('thumb as image'), 'target_link')->get();
        $this->serviceQuestion->setService($service);
        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->getSlug(),
            'thumb' => $service->thumb,
            'app_thumb' => $service->app_thumb,
            'banner' => $service->banner,
            'variable_type' => $service->variable_type,
            'questions' => $this->serviceQuestion->get(),
            'usp' => count($usps) > 0 ? $usps->pluck('name')->toArray() : null,
            'overview' => $service->contents ? $service->contents : null,
            'details' => $service->description,
            'partnership' => $partnership ? [
                'title' => $partnership->title,
                'short_description' => $partnership->short_description,
                'images' => count($partnership->slides) > 0 ? $partnership->slides->pluck('thumb') : []
            ] : null,
            'faqs' => $service->faqs ? json_decode($service->faqs) : null,
            'gallery' => count($galleries) > 0 ? $galleries : null,
            'blog' => count($blog_posts) > 0 ? $blog_posts : null
        ];
    }
}