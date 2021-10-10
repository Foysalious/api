<?php namespace Sheba\TopUp\Bulk\Validator;

use Sheba\TopUp\Bulk\Exception\InvalidExtension;

class ExtensionValidator extends Validator
{
    /**
     * @return bool
     * @throws InvalidExtension
     */
    public function check(): bool
    {
        $valid_extensions = ["xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw"];
        $extension = $this->file->getClientOriginalExtension();
        if (!in_array($extension, $valid_extensions))
            throw new InvalidExtension();

        return parent::check();
    }
}
