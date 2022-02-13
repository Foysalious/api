<?php namespace App\Sheba\Business\Appreciation;

use Sheba\Dal\Appreciation\Appreciation;
use Sheba\Dal\Appreciation\AppreciationRepository;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;

    /** @var AppreciationRepository $appreciationRepo */
    private $appreciationRepo;
    private $receiver;
    private $giver;
    private $sticker;
    private $complement;
    private $appreciation;

    public function __construct()
    {
        $this->appreciationRepo = app(AppreciationRepository::class);
    }

    public function setAppreciation(Appreciation $appreciation)
    {
        $this->appreciation = $appreciation;
        return $this;
    }

    public function setSticker($sticker)
    {
        $this->sticker = $sticker;
        return $this;
    }

    public function setComplement($complement)
    {
        $this->complement = $complement;
        return $this;
    }

    public function update()
    {
        $this->appreciationRepo->update($this->appreciation, [
            'sticker_id' => $this->sticker,
            'note' => $this->complement,
        ]);
    }
}