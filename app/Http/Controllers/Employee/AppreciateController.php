<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppreciateController extends Controller
{
    public function store(Request $request)
    {
        return api_response($request, null, 200);
    }

    public function categoryWiseStickers(Request $request)
    {
        $stickers = [
            [
                'category_id' => 1,
                'category_name' => 'thank_you',
                'category_title' => 'Thank You',
                'stickers' => [
                    [
                        'id'=> 1,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 2,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 3,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 4,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 5,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 6,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                ]
            ],
            [
                'category_id' => 2,
                'category_name' => 'life_saver',
                'category_title' => 'Life Saver',
                'stickers' => [
                    [
                        'id'=> 1,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 2,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 3,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                ]
            ],
            [
                'category_id' => 3,
                'category_name' => 'human_dynamo',
                'category_title' => 'Human Dynamo',
                'stickers' => [
                    [
                        'id'=> 1,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 2,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 3,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                ]
            ],
            [
                'category_id' => 4,
                'category_name' => 'foodie',
                'category_title' => 'Foodie',
                'stickers' => [
                    [
                        'id'=> 1,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 2,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 3,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                ]
            ],
            [
                'category_id' => 5,
                'category_name' => 'men_of_motivation',
                'category_title' => 'Men of Motivation',
                'stickers' => [
                    [
                        'id'=> 1,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 2,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 3,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                ]
            ],
            [
                'category_id' => 6,
                'category_name' => 'coolest_partner',
                'category_title' => 'Coolest Partner',
                'stickers' => [
                    [
                        'id'=> 1,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 2,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 3,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                ]
            ],
            [
                'category_id' => 7,
                'category_name' => 'idea_buzz',
                'category_title' => 'Idea Buzz',
                'stickers' => [
                    [
                        'id'=> 1,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 2,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 3,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                ]
            ],
            [
                'category_id' => 8,
                'category_name' => 'fashion_guru',
                'category_title' => 'Fashion Guru',
                'stickers' => [
                    [
                        'id'=> 1,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 2,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 3,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                ]
            ],
            [
                'category_id' => 9,
                'category' => 'sharp_cookie',
                'category_title' => 'Sharp Cookie',
                'stickers' => [
                    [
                        'id'=> 1,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 2,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                    [
                        'id'=> 3,
                        'image'=> 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg',
                    ],
                ]
            ],
        ];
        return api_response($request, $stickers, 200, ['stickers' => $stickers]);
    }
}