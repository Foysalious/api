<?php

use App\Models\Location;
use App\Models\Profile;
use Carbon\Carbon;
use Sheba\Dal\Category\Category;
use Sheba\Dal\Service\Service;

$common_seeds = [
    'created_by' => 1,
    'created_by_name' => 'IT - Shafiqul Islam',
    'updated_by' => 1,
    'updated_by_name' => 'IT - Shafiqul Islam',
    'created_at' => Carbon::now(),
    'updated_at' => Carbon::now()
];

$factory->define(Category::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds, [
        'name' => "Category #" . $faker->randomNumber(),
        'slug' => $faker->slug,
        'short_description' => $faker->text,
        'long_description' => $faker->paragraph,
        'thumb' => $faker->imageUrl(),
        'app_thumb' => $faker->imageUrl(),
        'banner' => $faker->imageUrl(),
        'app_banner' => $faker->imageUrl(),
        'video_link' => $faker->url
    ]);
});

$factory->define(Service::class, function (Faker\Generator $faker) use ($common_seeds) {
    $faqs = [];
    for ($i = 1; $i <= $faker->numberBetween($min = 1, $max = 5); $i++) {
        $question = [
            'question' => rtrim($faker->sentence, '.') . "?",
            'answer' => $faker->paragraph
        ];
        $faqs[] = $question;
    }

    return array_merge($common_seeds, [
        'name' => "Service #" . $faker->randomNumber(),
        'slug' => $faker->slug,
        'description' => $faker->paragraph,
        'app_thumb' => $faker->imageUrl(),
        'app_banner' => $faker->imageUrl(),
        'faqs' => json_encode($faqs),
    ]);
});

$factory->define(Location::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds, [
        'city_id' => 1,
        'name' => $faker->city,
        'geo_informations' => '{"lat":23.75655,"lng":90.387215,"radius":"1.1","geometry":{"type":"Polygon","coordinates":[[[90.3898,23.75835],[90.38458,23.75791],[90.38449,23.75685],[90.38445,23.75499],[90.3855,23.75495],[90.38664,23.755],[90.38877,23.75475],[90.38967,23.7566],[90.38998,23.758],[90.3898,23.75835],[90.3898,23.75835]]]},"center":{"lat":23.75655,"lng":90.387215}}',
        'publication_status' => 1,
        'is_published_for_partner' => 1,

    ]);
});

$factory->define(Profile::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds, [

        'name' => $faker->name,
        'mobile' =>'+8801678242955',
        'email' =>'tisha@sheba.xyz',
        'password' =>bcrypt('12345'),
        'is_blacklisted'=> 0,
        'mobile_verified'=>1,
        'email_verified'=>1,
        'nid_verification_request_count'=>0,
        'blood_group'=>'B+',
        'pro_pic' => $faker->image(),
        'total_asset_amount'=>$faker->randomNumber(),
        'monthly_living_cost'=>$faker->randomNumber(),
        'monthly_loan_installment_amount'=>$faker->randomNumber(),
    ]);
});