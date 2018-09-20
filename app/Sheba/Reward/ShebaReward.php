<?php

namespace Sheba\Reward;


abstract class ShebaReward
{
    abstract public function running();

    abstract public function upcoming();
}