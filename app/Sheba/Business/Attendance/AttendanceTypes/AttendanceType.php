<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Business\Attendance\AttendanceTypes\AttendanceSuccess;

abstract class AttendanceType
{
    /** @var AttendanceType */
    protected $next;
    /** @var AttendanceErrorList */
    protected $errors;

    public function setNext(AttendanceType $next)
    {
        $this->next = $next;
        return $this;
    }

    public function setError(AttendanceErrorList $errors)
    {
        if ($this->next) $this->next->setError($errors);
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return AttendanceErrorList
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return AttendanceSuccess | null
     */
    abstract function check();
}
