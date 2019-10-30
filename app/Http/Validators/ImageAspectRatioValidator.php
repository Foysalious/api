<?php namespace App\Http\Validators;

use Intervention\Image\Facades\Image;

class ImageAspectRatioValidator
{
    /** @var  Image $image */
    private $image;
    private $ratioWidth;
    private $ratioHeight;

    public function validate($attribute, $value, $parameters, $validator)
    {
        if($parameters[0] == "*") return true;
        $this->ratioWidth = explode(":", $parameters[0])[0];
        $this->ratioHeight = explode(":", $parameters[0])[1];
        $this->image = Image::make($value);

        return $this->isWidthMultipleOfRatioWidth() && $this->isHeightMultipleOfRatioHeight() && $this->isRatioEqual();
    }

    /**
     * @return bool
     */
    private function isWidthMultipleOfRatioWidth()
    {
        return (($this->image->width() % $this->ratioWidth) == 0);
    }

    /**
     * @return bool
     */
    private function isHeightMultipleOfRatioHeight()
    {
        return (($this->image->height() % $this->ratioHeight) == 0);
    }

    /**
     * @return bool
     */
    private function isRatioEqual()
    {
        return (($this->image->width() / $this->ratioWidth) == ($this->image->height() / $this->ratioHeight));
    }
}