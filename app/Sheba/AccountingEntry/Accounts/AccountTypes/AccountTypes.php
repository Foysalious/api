<?php


namespace Sheba\AccountingEntry\Accounts\AccountTypes;


abstract class AccountTypes
{
    public function __construct()
    {
        $typePrefix = "Sheba\AccountingEntry\Accounts\AccountTypes";
        $class      = get_class($this);
        $class_name = class_basename($class);
        foreach (get_class_vars($class) as $name => $val) {
            try {
                $this->$name = app("$typePrefix\\AccountKeys\\$class_name\\" . ucfirst(camel_case($name)));
            } catch (\Throwable $e) {
                return $e->getMessage();
            }
        }

    }

//    private function get_class_name($classname)
//    {
//        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
//        return $pos;
//    }

    public function getTypeName()
    {
        return pamelCase($this->get_class_name(get_class($this)));
    }

}
