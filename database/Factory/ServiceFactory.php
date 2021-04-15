<?php namespace Factory;


use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;

class ServiceFactory extends Factory
{
    protected function getModelClass()
    {
        return Service::class;
    }

    protected function getData()
    {
        $faqs = [];
        for ($i = 1; $i <= $this->faker->numberBetween($min = 1, $max = 5); $i++) {
            $question = [
                'question' => rtrim($this->faker->sentence, '.') . "?",
                'answer' => $this->faker->paragraph
            ];
            $faqs[] = $question;
        }

        return array_merge($this->commonSeeds, [
            'name' => "Service #" . $this->faker->randomNumber(),
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraph,
            'app_thumb' => $this->faker->imageUrl(),
            'app_banner' => $this->faker->imageUrl(),
            'faqs' => json_encode($faqs),
            'variable_type' => ServiceType::FIXED,
            'variables' => '{"price":"1700","min_price":"1000","max_price":"2500","description":""}',
            'publication_status' =>  1
        ]);
    }
}