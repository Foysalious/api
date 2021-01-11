<?php namespace Factories;

use Sheba\Dal\Category\Category;

class CategoryFactory extends Factory
{
    protected function getModelClass()
    {
        return Category::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'name' => "Category #" . $this->faker->randomNumber(),
            'slug' => $this->faker->slug,
            'short_description' => $this->faker->text,
            'long_description' => $this->faker->paragraph,
            'thumb' => $this->faker->imageUrl(),
            'app_thumb' => $this->faker->imageUrl(),
            'banner' => $this->faker->imageUrl(),
            'app_banner' => $this->faker->imageUrl(),
            'video_link' => $this->faker->url
        ]);
    }
}