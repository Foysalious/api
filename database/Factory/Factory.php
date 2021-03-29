<?php namespace Factory;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

abstract class Factory
{
    /** @var EloquentFactory */
    protected $factory;
    protected $commonSeeds;
    /** @var Carbon */
    protected $now;
    /** @var Generator */
    protected $faker;

    public function __construct(EloquentFactory $factory)
    {
        $this->factory = $factory;
    }

    public function handle()
    {
        $this->now = Carbon::now();
        $this->commonSeeds = [
            'created_by' => 1,
            'created_by_name' => 'IT - Shafiqul Islam',
            'updated_by' => 1,
            'updated_by_name' => 'IT - Shafiqul Islam',
            'created_at' => $this->now,
            'updated_at' => $this->now
        ];

        $this->factory->define($this->getModelClass(), function (Generator $faker) {
            $this->faker = $faker;
            return $this->getData();
        });
    }

    protected abstract function getModelClass();

    protected abstract function getData();
}