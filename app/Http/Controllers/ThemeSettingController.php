<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class ThemeSettingController extends Controller
{
    public function index()
    {
                $data= ' {
          "name": "theme-1",
          "primary_color": "#126C09",
          "secondary_color": "#126C09",
          "homepage": [
            {
                "type": "slider",
              "type_id": 5,
              "slide": [
                {
                    "url": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888928_app_slide.jpg",
                  "target": {
                    "type": "category",
                    "type_id": 4
                  }
                },
                {
                    "url": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888928_app_slide.jpg",
                  "target": {
                    "type": "category",
                    "type_id": null
                  }
                },
                {
                    "url": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888928_app_slide.jpg",
                  "target": null
                }
              ]
            },
            {
                "type": "all_categories",
              "category": [
                {
                    "id": 1,
                    "name":"Women Fashion",
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
                "name":"Men Fashion",
                    "id": 10,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                }
              ]
            },
            {
              "type": "collection",
              "type_id": 5,
              "collection_banner" : "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
              "product": [
                {
                  "id": 1,
                  "name":"Jacket",
                  "original_price": 550,
                  "vat_included_price": 550,
                  "vat_percentage": 0,
                  "unit": "piece",
                  "stock": 16,
                  "category_id": 225,
                  "discount_applicable": 0,
                  "discounted_amount": 0,
                  "discount_percentage": 0,
                  "image_gallery": [
                    {
                    "id": 314274,
                    "image_link": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg"
                    }
                  ]
                  "rating":4,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
                    "id": 10,
                    "name":"Product Name 2",
                  "original_price": 550,
                  "vat_included_price": 550,
                  "vat_percentage": 0,
                  "unit": "piece",
                  "stock": 16,
                  "category_id": 225,
                  "discount_applicable": 0,
                  "discounted_amount": 0,
                  "discount_percentage": 0,
                  "image_gallery": [
                    {
                    "id": 314274,
                    "image_link": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg"
                    }
                  ]
                  "rating": 5,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                }
              ]
            },
            {
                "type": "new_arrival",
              "product": [
                {
                    "id": 1,
                    "name": "Product Name 1",
                    "original_price": 550,
                  "vat_included_price": 550,
                  "vat_percentage": 0,
                  "unit": "piece",
                  "stock": 16,
                  "category_id": 225,
                  "discount_applicable": 0,
                  "discounted_amount": 0,
                  "discount_percentage": 0,
                  "image_gallery": [
                    {
                    "id": 314274,
                    "image_link": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg"
                    }
                  ]
                  "rating": 5,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
                    "id": 10,
                    "name": "Jacket",
                    "original_price": 550,
                  "vat_included_price": 550,
                  "vat_percentage": 0,
                  "unit": "piece",
                  "stock": 16,
                  "category_id": 225,
                  "discount_applicable": 0,
                  "discounted_amount": 0,
                  "discount_percentage": 0,
                  "image_gallery": [
                    {
                    "id": 314274,
                    "image_link": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg"
                    }
                  ]
                  "rating": 5,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                }
              ]
            },
            {
                "type": "trending",
              "product": [
                {
                  "id": 1,
                  "name": "Coat",
                 "original_price": 550,
                  "vat_included_price": 550,
                  "vat_percentage": 0,
                  "unit": "piece",
                  "stock": 16,
                  "category_id": 225,
                  "discount_applicable": 0,
                  "discounted_amount": 0,
                  "discount_percentage": 0,
                  "image_gallery": [
                    {
                    "id": 314274,
                    "image_link": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg"
                    }
                  ]
                  "rating": 5,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
                  "id": 10,
                  "name": "Jacket",
                  "original_price": 550,
                  "vat_included_price": 550,
                  "vat_percentage": 0,
                  "unit": "piece",
                  "stock": 16,
                  "category_id": 225,
                  "discount_applicable": 0,
                  "discounted_amount": 0,
                  "discount_percentage": 0,
                  "image_gallery": [
                    {
                    "id": 314274,
                    "image_link": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg"
                    }
                  ]
                  "rating": 5,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                }
              ]
            },
            {
                "type": "sale",
              "product": [
                {
                  "id": 1,
                  "name": "Jacket",
                  "original_price": 550,
                  "vat_included_price": 550,
                  "vat_percentage": 0,
                  "unit": "piece",
                  "stock": 16,
                  "category_id": 225,
                  "discount_applicable": 0,
                  "discounted_amount": 0,
                  "discount_percentage": 0,
                  "image_gallery": [
                    {
                    "id": 314274,
                    "image_link": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg"
                    }
                  ]
                  "rating": 5,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
                   "id": 10,
                  "name": "Coat",
                  "original_price": 550,
                  "vat_included_price": 550,
                  "vat_percentage": 0,
                  "unit": "unit",
                  "stock": 16,
                  "category_id": 225,
                  "discount_applicable": 0,
                  "discounted_amount": 0,
                  "discount_percentage": 0,
                  "image_gallery": [
                    {
                    "id": 314274,
                    "image_link": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg"
                    }
                  ]
                  "rating": 5,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                }
              ]
            },
            {
                "type": "category",
              "type_id": 5,
              "product": [
                {
                  "id": 1,
                  "name": "jacket",
                  "original_price": 550,
                  "vat_included_price": 550,
                  "vat_percentage": 0,
                  "unit": "piece",
                  "stock": 16,
                  "category_id": 225,
                  "discount_applicable": 0,
                  "discounted_amount": 0,
                  "discount_percentage": 0,
                  "image_gallery": [
                    {
                    "id": 314274,
                    "image_link": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg"
                    }
                  ]
                  "rating": 5,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
                  "id": 1,
                  "original_price": 550,
                  "vat_included_price": 550,
                  "vat_percentage": 0,
                  "unit": "piece",
                  "stock": 16,
                  "category_id": 225,
                  "discount_applicable": 0,
                  "discounted_amount": 0,
                  "discount_percentage": 0,
                  "image_gallery": [
                    {
                    "id": 314274,
                    "image_link": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg"
                    }
                  ]
                  "name": "Coat"
                  "id": 10,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                }
              ]
            },
            {
                "type": "banner",
              "url": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
              "target": {
                "type": "category",
                "type_id": 4
              }
            }
          ]
        }';
        return response()->json($data);
    }

}
