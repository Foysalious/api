<?php namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Location;
use App\Sheba\Schema\CategorySchema;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SchemaController extends Controller
{
    private $locations;
    private $cities;

    public function getFaqSchema(Request $request)
    {
        try {
            $this->validate($request, [
                'type' => 'required|string',
                'type_id' => 'required|integer',
            ]);
            $model = "App\\Models\\" . ucfirst(camel_case($request->type));
            $model = $model::find((int)$request->type_id);
            $faqs = json_decode($model->faqs, true) ?: [];
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
            return api_response($request, true, 200, ['faq_list' => $faq_lists]);
        } catch (ValidationException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getReviewSchema(Request $request)
    {
        try {
            $this->validate($request, [
                'type_id' => 'required|integer',
            ]);
            $category = Category::find((int)$request->type_id);
            $reviews = $category->reviews()->select('id', 'review_title', 'review', 'rating', 'category_id', 'created_by_name', 'created_at');
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
                "name" => $category->name,
                "image" => $category->thumb,
                "aggregateRating" => [
                    "@type" => "AggregateRating",
                    "ratingValue" => $reviews->avg('rating'),
                    "reviewCount" => $reviews->count()
                ],
                "review" => $lists
            ]);
            return api_response($request, true, 200, ['review_lists' => $review_lists]);
        } catch (ValidationException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getWebsiteSchema(Request $request)
    {
        try {
            $website = [];
            array_push($website, [
                "@context" => "http://schema.org",
                "@id" => "https://www.sheba.xyz/#website",
                "@type" => "WebSite",
                "name" => "Sheba.xyz",
                "alternateName" => "Sheba",
                "url" => "https://www.sheba.xyz/",
            ]);
            return api_response($request, true, 200, ['website' => $website]);
        } catch (ValidationException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getOrganisationSchema(Request $request)
    {
        try {
            $organisation = [];
            array_push($organisation, [
                "@context" => "http://schema.org",
                "@type" => "Organization",
                "name" => "Sheba.xyz",
                "legalName" => "Sheba Platform Limited.",
                "url" => "https://www.sheba.xyz/",
                "logo" => "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/images/sheba_logo_blue.png",
                "foundingDate" => "2015",
                "founders" => [
                    [
                        "@type" => "Person",
                        "name" => "Adnan Imtiaz Halim"
                    ],
                    [
                        "@type" => "Person",
                        "name" => "Ilmul Haque Sajib"
                    ],
                    [
                        "@type" => "Person",
                        "name" => "Abu Naser Shoaib"
                    ]
                ],
                "description" => "SHEBA.XYZ is the easiest way for you to hire verified and professional office and home service providers for all service needs.",
                "address" => [
                    "@type" => "PostalAddress",
                    "streetAddress" => "DevoTech Technology Park, Level 1, House 11, Road 113/A Gulshan 2",
                    "postOfficeBoxNumber" => "Gulshan Avenue",
                    "addressLocality" => "Dhaka",
                    "addressRegion" => "Dhaka",
                    "postalCode" => "1212",
                    "addressCountry" => "Bangladesh",
                    "contactType" => "customer support",
                    "telephone" => "+8809678016516",
                    "email" => "info@sheba.xyz",
                    "availableLanguage" => [
                        "English",
                        "Bengali"
                    ],
                    "areaServed" => "Bangladesh"
                ],
                "contactPoint" => [
                    "@type" => "ContactPoint",
                    "contactType" => "customer support",
                    "telephone" => "[+8809678016516]",
                    "email" => "info@sheba.xyz"
                ],
                "sameAs" => [
                    "https://www.facebook.com/sheba.xyz/",
                    "https://www.instagram.com/sheba.xyz.official/",
                    "https://www.youtube.com/channel/UCFknoAGYEBD0LqNQw1pd2Tg/",
                    "https://www.linkedin.com/company/sheba/",
                    "https://twitter.com/shebaforxyz?lang=en",
                    "https://www.pinterest.com/shebaxyz/",
                    "https://play.google.com/store/apps/details?id=xyz.sheba.customersapp",
                    "https://apps.apple.com/us/app/sheba-xyz/id1399019504",
                    "https://www.crunchbase.com/organization/sheba-xyz"
                ]
            ]);
            return api_response($request, true, 200, ['organisation' => $organisation]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAggregateReviewSchema(Request $request)
    {
        try {
            $this->validate($request, [
                'type_id' => 'required|integer',
            ]);
            $category = Category::find((int)$request->type_id);
            if (!$category->parent) {
                return api_response($request, true, 420);
            }
            $reviews = $category->reviews()->select('id', 'rating');
            $item_reviewed = [
                "@type" => "LocalBusiness",
                "address" => [
                    "@type" => "PostalAddress",
                    "addressLocality" => "Dhaka",
                    "addressRegion" => "Dhaka"
                ],
                "name" => "$category->name in Dhaka",
                "telephone" => "+8809678016516",
                "priceRange" => "৳৳৳",
                "description" => $category->meta_description,
                "URL" => "https://www.sheba.xyz/$category->slug",
                "Image" => $category->thumb
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
            return api_response($request, true, 200, ['review' => $review]);
        } catch (ValidationException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getCategorySchema(Request $request)
    {
        try {
            $this->validate($request, [
                'type_id' => 'required|integer',
            ]);
            $category = Category::find((int)$request->type_id);
            /*if (!$category->parent) {
                return api_response($request, true, 420);
            }*/
            $services = $category->publishedServices()->select('id', 'name', 'thumb');
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
                "name" => "Popular $category->name",
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
                "name" => "Other $category->name",
                "itemListElement" => $other_service
            ];
            array_push($item_list_elements, $popular_item_element, $other_item_element);

            $selected_city_ids = $category->locations()->pluck('city_id')->unique()->toArray();
            $cities = City::wherein('id', $selected_city_ids)->select('id', 'name')->get();
            $selected_city_names = [];
            foreach ($cities as $city) {
                $selected_city_names [] = $city->name;
            }

            $final_category = [
                "@context" => "http://schema.org/",
                "@type" => "Service",
                "serviceType" => $category->name,
                "description" => $category->meta_description,
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
                    "name" => implode(', ',$selected_city_names)
                ],
                "hasOfferCatalog" => [
                    "@type" => "OfferCatalog",
                    "name" => $category->name,
                    "itemListElement" => $item_list_elements
                ]
            ];
            return api_response($request, true, 200, ['category' => $final_category]);
        } catch (ValidationException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAllSchemas(Request $request, CategorySchema $category_schema)
    {
        try {
            $this->validate($request, [
                'type' => 'required|string',
                'type_id' => 'required|integer'
            ]);
            $schema_lists = $category_schema->setTypeID($request->type_id)->setType($request->type)->generate();
            return api_response($request, true, 200, ['schema_lists' => $schema_lists]);
        } catch (ValidationException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}