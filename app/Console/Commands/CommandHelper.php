<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class CommandHelper
{
    /** @var Command */
    private $command;

    public function setCommand(Command $command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return InputArgument[]
     */
    public function getInputArguments()
    {
        return $this->command->getDefinition()->getArguments();
    }

    /**
     * @return string[]
     */
    public function getInputArgumentNames()
    {
        return array_values(array_map(function(InputArgument $input) {
            return $input->getName();
        }, $this->getInputArguments()));
    }
}
