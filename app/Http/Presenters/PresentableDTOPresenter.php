<?php namespace App\Http\Presenters;

use Sheba\PresentableDTO;

class PresentableDTOPresenter extends Presenter
{
    /** @var PresentableDTO */
    private $dto;

    public function __construct(PresentableDTO $dto)
    {
        $this->dto = $dto;
    }

    public function toArray()
    {
        return $this->dto->toArray();
    }
}
