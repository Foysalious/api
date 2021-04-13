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
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
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
              "product": [
                {
                    "id": 1,
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
                    "id": 10,
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
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
                    "id": 10,
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
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
                    "id": 10,
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
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
                    "id": 10,
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
                  "icon": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_app_slide.jpg",
                  "banner": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/slides/1612888957_slide.jpg"
                },
                {
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
