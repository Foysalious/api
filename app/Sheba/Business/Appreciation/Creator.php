<?php namespace App\Sheba\Business\Appreciation;

use App\Jobs\Business\AppreciationPushNotification;
use Sheba\Dal\Appreciation\Appreciation;
use Sheba\Dal\Appreciation\AppreciationRepository;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;

    /** @var AppreciationRepository $appreciationRepo */
    private $appreciationRepo;
    private $receiver;
    private $giver;
    private $sticker;
    private $complement;

    public function __construct()
    {
        $this->appreciationRepo = app(AppreciationRepository::class);
    }

    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function setGiver($giver)
    {
        $this->giver = $giver;
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

    public function create()
    {
        $appreciation = $this->appreciationRepo->create([
            'receiver_id' => $this->receiver,
            'giver_id' => $this->giver,
            'sticker_id' => $this->sticker,
            'note' => $this->complement,
        ]);
        $this->sendPushToAppreciationReceiver($appreciation);
        return $appreciation;
    }

    private function sendPushToAppreciationReceiver(Appreciation $appreciation)
    {
        dispatch(new AppreciationPushNotification($appreciation));
    }
}