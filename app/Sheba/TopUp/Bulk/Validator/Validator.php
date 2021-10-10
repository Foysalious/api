<?php namespace Sheba\TopUp\Bulk\Validator;

use Illuminate\Http\UploadedFile;

abstract class Validator
{
    /** @var UploadedFile $file */
    protected $file;
    /** @var Validator $next */
    private $next;

    /**
     * @param Validator $next
     * @return Validator
     */
    public function linkWith(Validator $next): Validator
    {
        $this->next = $next;
        $this->next->setFile($this->file);

        return $next;
    }

    /**
     * @param UploadedFile $file
     * @return $this
     */
    public function setFile(UploadedFile $file): Validator
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        if (!$this->next) {
            return true;
        }

        return $this->next->check();
    }
}
