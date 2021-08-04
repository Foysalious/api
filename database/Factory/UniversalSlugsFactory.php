<?php namespace Factory;


use Sheba\Dal\UniversalSlug\Model;

class UniversalSlugsFactory extends Factory
{
    protected function getModelClass()
    {
        return Model::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'slug' => $this->faker->text
        ]);
    }

}