<?php namespace Sheba\Partner;


use App\Models\Partner;

class BadgeResolver
{

    /**
     * Partner $partner
     */
    private $partner;

    private $portalName;
    private $userId;
    private $versionCode;
    private $userAgent;

    public function __construct()
    {
        $this->portalName = request()->header('portal-name');
        $this->userId = request()->header('user-id');
        $this->versionCode = request()->header('version-code');
        $this->userAgent = request()->header('user-agent');
    }

    /**
     * @param Partner $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function resolve()
    {
        $this->resolveVersionWiseBadge($this->partner);
    }

    /**
     * @param $partner
     * @return Partner $partner
     */
    private function resolveVersionWiseBadge(Partner &$partner)
    {
        $this->resolveUserAgent();
        if($this->userAgent) {
            switch ($this->userAgent) {
                case 'android' && $this->versionCode <= 30115:
                    $partner['subscription_type'] = $this->setBadgeName($partner->badge);
                    break;
                default:
                    $partner['subscription_type'] =  $partner->subscription ? $partner->subscription->name : null;
                    break;
            }
        }
        $partner['badge'] = $partner->badge;
        return $partner;
    }

    private function resolveUserAgent()
    {
        if($this->userAgent) {
            $possible_user_agents = ['android','ios'];
            $this->userAgent = strtolower($this->userAgent);
            foreach ($possible_user_agents as $possible_user_agent) {
                if(strpos($this->userAgent, $possible_user_agent) !== false) {
                    $this->userAgent = $possible_user_agent;
                    break;
                }
            }
        }
    }

    /**
     * @param $badge
     * @return string
     */
    private function setBadgeName($badge)
    {
        $partner_showable_badge = constants('PARTNER_BADGE');

        if ($badge === $partner_showable_badge['gold']) return 'ESP';
        else if ($badge === $partner_showable_badge['silver']) return 'PSP';
        else return 'LSP';
    }
}