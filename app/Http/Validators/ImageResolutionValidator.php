<?php namespace App\Http\Validators;

use Intervention\Image\Facades\Image;

class ImageResolutionValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        $resX = explode("x", $parameters[0])[0];
        $resY = explode("x", $parameters[0])[1];
        $img = Image::make($value);
        return ($img->height() >= $resY) && ($img->width() >= $resX);
    }
}