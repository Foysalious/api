<?php namespace App\Sheba\Schema;


use App\Models\Category;
use App\Models\City;

class CategorySchema
{
    private $category;
    private $services;
    private $type;
    private $typeId;

    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setTypeID($type_id)
    {
        $this->typeId = (int)$type_id;
        return $this;
    }

    private function getReviews()
    {
        $reviews = $this->getModel()->reviews()->select('id', 'review_title', 'review', 'rating', 'category_id', 'created_by_name', 'created_at');
        return $reviews;
    }

    private function getServices()
    {
        $services = $this->getModel()->publishedServices()->select('id', 'name', 'thumb');
        return $services;
    }

    private function getModel()
    {
        $model = "App\\Models\\" . ucfirst($this->type);
        $model = $model::find($this->typeId);
        return $model;
    }

    public function generate()
    {
        $this->setCategory($this->getModel());
        $review = $this->getAggregateReviewSchema();
        $review_lists = $this->getReviewSchema();
        $final_category = $this->getCategorySchema();
        $faq_lists = $this->getFaqSchema();
        return [ 'review' => $review, 'review_lists' => $review_lists, 'final_category' => $final_category, 'faq_lists' => $faq_lists];
    }

    private function getReviewSchema()
    {
        $reviews = $this->getReviews();
        $review_lists = [];
        $lists = [];
        foreach ($reviews->get() as $review) {
            array_push($lists, [
                "@type" => "Review",
                "author" => $review->created_by_name,
                "datePublished" => $review->created_at->format('Y-m-d'),
                "description" => $review->review,
                "name" => $review->review_title,
                "reviewRating" => [
                    "@type" => "Rating",
                    "ratingValue" => $review->rating
                ]
            ]);
        }
        array_push($review_lists, [
            "@context" => "http://schema.org",
            "@type" => "LocalBusiness",
            "name" => $this->category->name,
            "image" => $this->category->thumb,
            "aggregateRating" => [
                "@type" => "AggregateRating",
                "ratingValue" => $reviews->avg('rating'),
                "reviewCount" => $reviews->count()
            ],
            "review" => $lists
        ]);
        return $review_lists;
    }

    private function getAggregateReviewSchema()
    {
        $reviews = $this->getReviews();
        $item_reviewed = [
            "@type" => "LocalBusiness",
            "address" => [
                "@type" => "PostalAddress",
                "addressLocality" => "Dhaka",
                "addressRegion" => "Dhaka"
            ],
            "name" => $this->category->name." in Dhaka",
            "telephone" => "+8809678016516",
            "priceRange" => "৳৳৳",
            "description" => $this->category->meta_description,
            "URL" => "https://www.sheba.xyz/".$this->category->slug,
            "Image" => $this->category->thumb
        ];
        $review_rating = [
            "@type" => "AggregateRating",
            "bestRating" => "5",
            "worstRating" => "1",
            "ratingCount" => $reviews->count(),
            "ratingValue" => (double)$reviews->avg('rating'),
            "itemReviewed" => [
                "@type" => "Thing",
                "name" => "ServiceReview"
            ]
        ];
        $review = [
            "@context" => "http://schema.org",
            "@type" => "Review",
            "itemReviewed" => $item_reviewed,
            "author" => "Users",
            "ReviewRating" => $review_rating
        ];
        return $review;
    }

    private function getCategorySchema()
    {
        $services = $this->getServices();
        $item_list_elements = [];
        $popular_service = [];
        foreach ($services->limit(4)->get() as $service) {
            array_push($popular_service, [
                "@type" => "Offer",
                "itemOffered" => [
                    "@type" => "Service",
                    "name" => $service->name,
                    "image" => $service->thumb
                ]
            ]);
        }
        $popular_item_element = [
            "@type" => "OfferCatalog",
            "name" => "Popular ".$this->category->name,
            "itemListElement" => $popular_service
        ];
        $other_service = [];
        foreach ($services->limit(4)->get() as $service) {
            array_push($other_service, [
                "@type" => "Offer",
                "itemOffered" => [
                    "@type" => "Service",
                    "name" => $service->name,
                    "image" => $service->thumb
                ]
            ]);
        }
        $other_item_element = [
            "@type" => "OfferCatalog",
            "name" => "Other ".$this->category->name,
            "itemListElement" => $other_service
        ];
        array_push($item_list_elements, $popular_item_element, $other_item_element);

        $selected_city_ids = $this->category->locations()->pluck('city_id')->unique()->toArray();
        $cities = City::wherein('id', $selected_city_ids)->select('id', 'name')->get();
        $selected_city_names = [];
        foreach ($cities as $city) {
            $selected_city_names [] = $city->name;
        }

        $final_category = [
            "@context" => "http://schema.org/",
            "@type" => "Service",
            "serviceType" => $this->category->name,
            "description" => $this->category->meta_description,
            "provider" => [
                "@type" => "LocalBusiness",
                "name" => "Sheba.xyz",
                "address" => "113/A Gulshan 2, Dhaka",
                "priceRange" => "৳৳৳",
                "telephone" => "+8809678016516",
                "image" => "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/images/sheba_logo_blue.png"
            ],
            "areaServed" => [
                "@type" => "State",
                "name" => implode(', ', $selected_city_names)
            ],
            "hasOfferCatalog" => [
                "@type" => "OfferCatalog",
                "name" => $this->category->name,
                "itemListElement" => $item_list_elements
            ]
        ];
        return $final_category;
    }

    private function getFaqSchema()
    {
        $faqs = json_decode($this->category->faqs, true) ?: [];
        $faq_lists = [];
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
        array_push($faq_lists, [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $lists
        ]);
        return $faq_lists;
    }
}