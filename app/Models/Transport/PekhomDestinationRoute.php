<?php namespace App\Models\Transport;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;;

class PekhomDestinationRoute extends Eloquent
{
    protected $connection = 'mongodb_atlas_conn';
}
