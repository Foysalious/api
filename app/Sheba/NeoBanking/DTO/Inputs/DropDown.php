<?php


namespace Sheba\NeoBanking\DTO\Inputs;


class DropDown extends EditText
{
    protected $field_type = 'dropdown';
    protected $list_type  = 'dialog';
    /** @var array $list */
    protected $list;

    /**
     * @param string $list_type
     * @return DropDown
     */
    public function setListType($list_type)
    {
        $this->list_type = $list_type;
        return $this;
    }

    /**
     * @param array $list
     * @return DropDown
     */
    public function setList($list)
    {
        $this->list = $list;
        return $this;
    }

}
