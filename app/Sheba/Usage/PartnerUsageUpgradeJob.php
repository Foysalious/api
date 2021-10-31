<?php

namespace App\Sheba\Usage;

use App\Jobs\Job;
use App\Models\Partner;
use App\Models\PartnerUsageHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Sheba\ModificationFields;
use Sheba\Usage\Usage;

class PartnerUsageUpgradeJob extends Job implements ShouldQueue
{
    use ModificationFields; 
    use InteractsWithQueue, SerializesModels;

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
        if ($this->attempts() <= 1){
            try {
                $data = ['type' => $this->type];
                if (!empty($modifier))
                    $this->setModifier($modifier);
                else
                    $this->setModifier($this->user);
                $data['partner_id'] = $this->user->id;
                PartnerUsageHistory::create($this->withCreateModificationField($data));
                if (!empty($this->user->referredBy))
                    (new Usage())->setType($this->type)->setUser($this->user)->updateUserLevel();
            }catch (\Throwable $e){
                Log::error($e->getFile().'||'.$e->getLine().'||'.$e->getMessage());
            }
        }
    }
}