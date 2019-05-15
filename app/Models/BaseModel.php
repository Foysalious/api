<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    public function clean()
    {
        foreach ($this->attributes as $key => $value) {
            if (array_key_exists($key, $this->original)) {
                $this->attributes[$key] = $this->original[$key];
            }
        }
    }
}