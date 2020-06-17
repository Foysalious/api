<?php namespace Sheba\AutoSpAssign\Parameter;


interface Parameter
{
    public function getWeight(): double;

    public function getScore($value): double;
}