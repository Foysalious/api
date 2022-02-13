<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;
use Illuminate\Support\Collection;

abstract class Factory extends EloquentFactory
{
    /** @var array $commonSeeds */
    protected $commonSeeds;
    /** @var Carbon */
    protected $now;

    public function __construct(
        $count = null,
        ?Collection $states = null,
        ?Collection $has = null,
        ?Collection $for = null,
        ?Collection $afterMaking = null,
        ?Collection $afterCreating = null,
        $connection = null
    ) {
        $this->now = Carbon::now();

        $this->commonSeeds = [
            'created_by' => 1,
            'created_by_name' => 'IT - Shafiqul Islam',
            'updated_by' => 1,
            'updated_by_name' => 'IT - Shafiqul Islam',
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];

        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection);
    }
}
