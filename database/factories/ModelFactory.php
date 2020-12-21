<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use Carbon\Carbon;

$common_seeds = [
    'created_by' => 1,
    'created_by_name' => 'IT - Shafiqul Islam',
    'updated_by' => 1,
    'updated_by_name' => 'IT - Shafiqul Islam',
    'created_at' => Carbon::now(),
    'updated_at' => Carbon::now()
];

$factory->define(\App\Models\Category::class, function (Faker\Generator $faker) use ($common_seeds) {
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

$factory->define(\Sheba\Dal\Service\Service::class, function (Faker\Generator $faker) use ($common_seeds) {
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
