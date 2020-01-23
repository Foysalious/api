<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SluggableType extends Model
{
    protected $table = 'universal_slugs';
    protected $guarded = ['id'];
}