<?php namespace Sheba\Cache\Schema\DataBuilder;


use App\Models\Category;
use App\Models\City;
use App\Models\ReviewQuestionAnswer;
use Sheba\Dal\MetaTag\MetaTagRepositoryInterface;
use DB;

class CategorySchema
{
    /** @var Category */
    private $category;
    private $metaTagRepository;
    private $metaTag;
    private $categoryReview;
    const REVIEW_SCHEMA_NAME = 'review';
    const AGGREGATE_REVIEW_SCHEMA_NAME = 'aggregate_review';
    const FAQ_SCHEMA_NAME = 'faq';
    const CATEGORY_SCHEMA_NAME = 'service';
    const BREADCRUMB_SCHEMA_NAME = 'breadcrumb';

    public function __construct(MetaTagRepositoryInterface $meta_tag_repository)
    {
        $this->metaTagRepository = $meta_tag_repository;
    }

    private function setCategoryReview($categoryReview)
    {
        $this->categoryReview = $categoryReview;
        return $this;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
        $this->metaTag = $this->metaTagRepository->builder()->select('meta_tag')->where('taggable_type', 'like', '%category')->where('taggable_id', $this->category->id)->first();
        return $this;
    }

    private function getServices()
    {
        return $this->category->publishedServices()->select('id', 'name', 'thumb')->limit(4)->get();
    }

    public function get()
    {
        return $this->generate();
    }

    private function generate()
    {
        return [
            self::AGGREGATE_REVIEW_SCHEMA_NAME => $this->getAggregateReviewSchema(),
            self::REVIEW_SCHEMA_NAME => $this->getReviewSchema(),
            self::CATEGORY_SCHEMA_NAME => $this->getCategorySchema(),
            self::FAQ_SCHEMA_NAME => $this->getFaqSchema(),
            self::BREADCRUMB_SCHEMA_NAME => $this->getBreadCrumbSchema(),
        ];
    }

    private function getAggregateReviewSchema()
    {
        $reviews = $this->category->reviews()->selectRaw("count(reviews.id) as total_ratings")->selectRaw("avg(reviews.rating) as avg_rating")->first();
        $this->setCategoryReview($reviews);
        $item_reviewed = [
            "@type" => "LocalBusiness",
            "address" => [
                "@type" => "PostalAddress",
                "addressLocality" => "Dhaka",
                "addressRegion" => "Dhaka"
            ],
            "name" => $this->category->name . " in Dhaka",
            "telephone" => "+8809678016516",
            "priceRange" => "৳৳৳",
            "description" => $this->metaTag && $this->metaTag->meta_tag ? json_decode($this->metaTag->meta_tag)->description : null,
            "URL" => config('sheba.front_url') . '/' . $this->category->getSlug(),
            "Image" => $this->category->thumb
        ];
        $review_rating = [
            "@type" => "AggregateRating",
            "bestRating" => "5",
            "worstRating" => "1",
            "ratingCount" => $reviews->total_ratings ? (int)$reviews->total_ratings : 0,
            "ratingValue" => $reviews->avg_rating ? round($reviews->avg_rating, 2) : 5,
            "itemReviewed" => [
                "@type" => "Thing",
                "name" => "ServiceReview"
            ]
        ];
        return [
            "@context" => "http://schema.org",
            "@type" => "Review",
            "itemReviewed" => $item_reviewed,
            "author" => "Users",
            "ReviewRating" => $review_rating
        ];
    }

    private function getReviewSchema()
    {
        $reviews = $this->getReviews();
        $lists = [];
        foreach ($reviews as $review) {
            array_push($lists, [
                "@type" => "Review",
                "author" => $review->customer_name,
                "datePublished" => $review->created_at->format('Y-m-d'),
                "description" => $review->review,
                "name" => $review->review_title,
                "reviewRating" => [
                    "@type" => "Rating",
                    "ratingValue" => $review->rating
                ]
            ]);
        }
        return [
            "@context" => "http://schema.org",
            "@type" => "LocalBusiness",
            "name" => $this->category->name,
            "image" => $this->category->thumb,
            "aggregateRating" => [
                "@type" => "AggregateRating",
                "ratingValue" => $this->categoryReview->avg_rating ? round($this->categoryReview->avg_rating, 2) : 5,
                "reviewCount" => $this->categoryReview->total_ratings ? $this->categoryReview->total_ratings : 0
            ],
            "review" => $lists
        ];
    }

    private function getReviews()
    {
        return ReviewQuestionAnswer::select('reviews.category_id', 'customer_id', 'partner_id', 'reviews.rating', 'review_title', 'reviews.created_at')
            ->selectRaw("profiles.name as customer_name,rate_answer_text as review,review_id as id,pro_pic as customer_picture,jobs.created_at as order_created_at")
            ->join('reviews', 'reviews.id', '=', 'review_question_answer.review_id')
            ->join('customers', 'customers.id', '=', 'reviews.customer_id')
            ->join('jobs', 'jobs.id', '=', 'reviews.job_id')
            ->join('profiles', 'profiles.id', '=', 'customers.profile_id')
            ->where('review_type', 'like', '%' . '\\Review')
            ->where('review_question_answer.rate_answer_text', '<>', '')
            ->whereRaw("CHAR_LENGTH(rate_answer_text)>20")
            ->whereIn('reviews.rating', [5])
            ->where('reviews.category_id', $this->category->id)
            ->take(10)
            ->orderBy('id', 'desc')
            ->groupBy('customer_id')
            ->get();
    }

    private function getCategorySchema()
    {
        $services = $this->getServices();
        $item_list_elements = [];
        $popular_service = [];
        foreach ($services as $service) {
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
            "name" => "Popular " . $this->category->name,
            "itemListElement" => $popular_service
        ];
        $other_service = [];
        foreach ($services as $service) {
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
            "name" => "Other " . $this->category->name,
            "itemListElement" => $other_service
        ];
        array_push($item_list_elements, $popular_item_element, $other_item_element);

        $selected_city_ids = $this->category->locations()->pluck('city_id')->unique()->toArray();
        $cities = City::wherein('id', $selected_city_ids)->select('id', 'name')->get();
        $selected_city_names = [];
        foreach ($cities as $city) {
            $selected_city_names [] = $city->name;
        }

        return [
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
    }

    private function getFaqSchema()
    {
        $faqs = $this->category->faqs ? json_decode($this->category->faqs, true) : [];
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

    private function getBreadCrumbSchema()
    {
        $marketplace_url = config('sheba.front_url');
        $items = [
            [
                'name' => 'Sheba Platform Limited',
                'url' => $marketplace_url
            ]
        ];
        if ($this->category->isParent()) {
            array_push($items, [
                'name' => $this->category->name,
                'url' => $marketplace_url . '/' . $this->category->getSlug(),
            ]);
        } else {
            $master = Category::select('name')->where('id', (int)$this->category->parent_id)->first();
            array_push($items, [
                'name' => $master->name,
                'url' => $marketplace_url . '/' . $master->getSlug(),
            ], [
                'name' => $this->category->name,
                'url' => $marketplace_url . '/' . $this->category->getSlug(),
            ]);
        }
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