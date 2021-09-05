<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class ThemeSettingController extends Controller
{
    public function index()
    {
        $data= '{
  "name": "theme-1",
  "primary_color": "#126C09",
  "secondary_color": "#126C09",
  "homepage": [
    {
      "type": "slider",
      "type_id": 5,
      "slide": [
        {
          "url": "https://cdn-shebadev.s3.ap-south-1.amazonaws.com/image%2049.png",
          "target": {
            "type": "category",
            "type_id": 4
          }
        },
        {
          "url": "https://cdn-shebadev.s3.amazonaws.com/548.4495706096466user261629964306657",
          "target": {
            "type": "category",
            "type_id": null
          }
        },
        {
          "url": "https://cdn-shebadev.s3.ap-south-1.amazonaws.com/227.81362452631916user261629964345377",
          "target": null
        }
      ]
    },
    {
      "type": "all_categories",
      "name": "All Categories",
      "category": [
        
         {
          "name": "Men Fashion",
          "id": 10,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },

        {
          "name": "Men Fashion",
          "id": 2,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },
        {
          "name": "Men Fashion",
          "id": 10052,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        }
      ]
    },
    {
      "type": "collection",
      "type_id": 5,
      "name": "Collection Name",
      "collection_banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
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
          "rating": 4,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-shttps://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },
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
          "rating": 4,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-shttps://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },
        
        {
          "id": 10,
          "name": "Product Name 2",
          "original_price": 550,
          "vat_included_price": 550,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 0,
          "discounted_amount": 0,
          "discount_percentage": 0,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        }
      ]
    },
    {
      "type": "new_arrival",
      "name": "New Arrival",
      "product": [
        {
          "id": 1,
          "name": "Product Name 1",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },
        {
          "id": 2,
          "name": "Product Name 2",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },
        {
          "id": 1,
          "name": "Product Name 1",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },{
          "id": 1,
          "name": "Product Name 1",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },{
          "id": 1,
          "name": "Product Name 1",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },{
          "id": 1,
          "name": "Product Name 1",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },{
          "id": 1,
          "name": "Product Name 1",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },{
          "id": 1,
          "name": "Product Name 1",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },{
          "id": 1,
          "name": "Product Name 1",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },{
          "id": 1,
          "name": "Product Name 1",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },{
          "id": 1,
          "name": "Product Name 1",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },{
          "id": 1,
          "name": "Product Name 1",
          "original_price": 2990,
          "vat_included_price": 2990,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 1,
          "discounted_amount": 2890,
          "discount_percentage": 3.3,
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        }
      ]
    },
    {
      "type": "trending",
      "name": "Trending",
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
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
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
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        }
      ]
    },
    {
      "type": "sale",
      "name": "Sales",
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
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
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
          "rating": 5,
          "count_rating": 7,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        }
      ]
    },
    {
      "type": "category",
      "type_id": 5,
      "name": "Category Name ",
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
          "rating": 5,
          "count_rating": 72,
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        },
        {
          "id": 10,
          "original_price": 550,
          "vat_included_price": 550,
          "vat_percentage": 0,
          "unit": "piece",
          "stock": 16,
          "category_id": 225,
          "discount_applicable": 0,
          "discounted_amount": 0,
          "discount_percentage": 0,
          "count_rating": 72,
          "rating": 5,
          "name": "Coat",
          "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
          "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/1608693744_jacket.jpeg",
          "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
        }
      ]
    },
    {
      "type": "banner",
      "name": "Banner",
      "url": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
      "target": {
        "type": "category",
        "type_id": 4
      }
    }
  ]
} ';
        return response()->json($data);
    }

}
