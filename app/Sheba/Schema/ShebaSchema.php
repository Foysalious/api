<?php namespace Sheba\Schema;


class ShebaSchema
{

    public function get()
    {
        return [
            'website' => [
                "@context" => "http://schema.org",
                "@id" => "https://www.sheba.xyz/#website",
                "@type" => "WebSite",
                "name" => "Sheba.xyz",
                "alternateName" => "Sheba",
                "url" => "https://www.sheba.xyz/",
            ],
            'organization' => [
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
            ]
        ];
    }


}