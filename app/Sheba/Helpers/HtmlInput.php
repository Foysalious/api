<?php namespace Sheba\Helpers;

class HtmlInput
{
    /**
     * @return array
     */
    public static function types()
    {
        return [
            'button',
            'checkbox',
            'color',
            'date',
            'datetime',
            'email',
            'file',
            'hidden',
            'image',
            'month',
            'number',
            'password',
            'radio',
            'range',
            'reset',
            'search',
            'submit',
            'tel',
            'text',
            'textarea',
            'time',
            'url',
            'week'
        ];
    }

    /**
     * @return array
     */
    public static function typesForFormBuilder()
    {
        return [
            'checkbox',
            'date',
            'datetime',
            'email',
            'number',
            'radio',
            'range',
            'text',
            'textarea'
        ];
    }

    /**
     * @return array
     */
    public static function attributesForFormBuilder()
    {
        return [
            'min',
            'max',
            'multiple',
            'placeholder',
            'required',
            'step'
        ];
    }
}