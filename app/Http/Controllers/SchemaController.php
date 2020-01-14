<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SchemaController extends Controller
{
    public function getFaqSchema(Request $request)
    {
        try {
            $this->validate($request, [
                'type' => 'required|string',
                'type_id' => 'required|integer',
            ]);
            $model = "App\\Models\\" . ucfirst(camel_case($request->type));
            $model = $model::find((int)$request->type_id);
            $faqs = json_decode($model->faqs, true);
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
                    "https://play.google.com/store/apps/details?id=xyz.sheba.customersapp",
                    "https://www.crunchbase.com/organization/sheba-xyz"
                ]
            ]);
            return api_response($request, true, 200, ['organisation' => $organisation]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}