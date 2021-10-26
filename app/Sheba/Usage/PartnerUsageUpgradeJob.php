<?php

namespace App\Sheba\Usage;

use App\Jobs\Job;
use App\Models\Partner;
use App\Models\PartnerUsageHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\ModificationFields;
use Sheba\Usage\Usage;

class PartnerUsageUpgradeJob extends Job implements ShouldQueue
{
    use ModificationFields;

    private $modifier;
    private $user;
    private $type;

    public function __construct($user, $modifier, $type)
    {
        $this->type     = $type;
        $this->user     = $user;
        $this->modifier = $modifier;
    }

    public function handle()
    {

        $data = ['type' => $this->type];
        if (!empty($modifier))
            $this->setModifier($modifier);
        $data['partner_id'] = $this->user->id;
        PartnerUsageHistory::create($this->withCreateModificationField($data));
        if (!empty($this->user->referredBy))
            (new Usage())->setType($this->type)->setUser($this->user)->updateUserLevel();
    }


}