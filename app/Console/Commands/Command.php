<?php namespace App\Console\Commands;

use Illuminate\Console\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;

abstract class Command extends BaseCommand
{
    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @return InputArgument[]
     */
    public function getInputArguments()
    {
        return $this->getCommandHelper()->getInputArguments();
    }

    /**
     * @return string[]
     */
    public function getInputArgumentNames()
    {
        return $this->getCommandHelper()->getInputArgumentNames();
    }

    /**
     * @return CommandHelper
     */
    protected function getCommandHelper()
    {
        /** @var CommandHelper $command_helper */
        $command_helper = app(CommandHelper::class);
        return $command_helper->setCommand($this);
    }
}
