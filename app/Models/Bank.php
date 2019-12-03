<<<<<<< HEAD
<?php namespace App\Models;
=======
<?php

namespace App\Models;
>>>>>>> 9b066f557b2b02a5a3705222031861f84382d849

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
<<<<<<< HEAD
    protected $guarded  = ['id'];

}
=======
    protected $guarded=['id'];
    public function users(){
        return $this->hasMany(BankUser::class);
    }
}
>>>>>>> 9b066f557b2b02a5a3705222031861f84382d849
