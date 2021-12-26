<?php namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;

abstract class Factory extends EloquentFactory
{
    protected $commonSeeds;
    /** @var Carbon */
    protected $now;

    public function __construct()
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

        parent::__construct();
    }
}
