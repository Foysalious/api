<?php


namespace Sheba\AccountingEntry\Accounts;


abstract class RootAccounts
{
    public function __construct()
    {
        $typePrefix = "Sheba\AccountingEntry\Accounts";
        $class      = get_class($this);
        foreach (get_class_vars($class) as $name => $val) {
            try {
                $this->$name = app("$typePrefix\\AccountTypes\\" . ucfirst(camel_case($name)));
            } catch (\Throwable $e) {

            }
        }
    }
}
