<?php namespace App\Sheba\LightOnBoarding;

use App\Exceptions\InvalidModeratorException;
use App\Exceptions\ModeratorDistanceExceedException;
use App\Exceptions\NotFoundException;
use App\Models\Affiliate;
use App\Models\Partner;
use DB;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;

class PartnerModerator
{
    private $affiliate, $partner, $moderator;
    private $affiliationRewards;
    private $moderatorRole;
    private $distanceThreshold;

    public function __construct($moderatorRole)
    {
        $this->moderatorRole = $moderatorRole;
        $this->affiliationRewards = new AffiliationRewards();
        $this->distanceThreshold = constants('MODERATOR_DISTANCE_THRESHOLD');
    }

    public function getPartner()
    {
        return $this->partner;
    }

    public function getAffiliate()
    {
        return $this->affiliate;
    }

    /**
     * @param Affiliate $affiliate
     * @return $this
     * @throws InvalidModeratorException
     */
    public function setModerator(Affiliate $affiliate)
    {
        $this->moderator = $affiliate;
        if (!$this->moderator->is_moderator) {
            throw new InvalidModeratorException();
        }
        return $this;
    }

    /**
     * @param $partner_id
     * @return $this
     * @throws InvalidModeratorException
     * @throws NotFoundException
     */
    public function setPartner($partner_id)
    {
        $this->partner = Partner::find($partner_id);
        $this->affiliate = $this->partner->affiliate;
        if (empty($this->partner)) {
            throw new NotFoundException('Partner Does not exists', 404);
        }
        if (empty($this->affiliate)) {
            throw new InvalidModeratorException('This partner does not have any affiliate');
        }
        return $this;
    }

    /**
     * @param $data
     * @return bool
     * @throws InvalidModeratorException
     * @throws ModeratorDistanceExceedException
     * @throws \Exception
     */
    private function validateRequest($data)
    {
        if ($this->partner->moderation_status && $this->partner->moderation_status != 'pending') {
            throw new InvalidModeratorException('This partner is already moderated');
        }
        if ($this->moderatorRole == 'moderator') {
            if ($this->moderator->id != $this->partner->moderator_id) {
                throw new InvalidModeratorException('You are not a moderator of this partner');
            }
            $latLng = ['lat' => $data['lat'], 'lng' => $data['lng']];
            $this->validateLocation($latLng, $this->partner);
        }
        return true;
    }

    /**
     * @param $data
     * @throws InvalidModeratorException
     * @throws ModeratorDistanceExceedException
     * @throws \Throwable
     */
    public function accept($data)
    {
        try {
            if ($this->validateRequest($data)) {
                DB::beginTransaction();
                $this->setPartnerData('approved');
                $this->affiliationRewards->setAffiliate($this->partner->affiliate)
                    ->payAffiliate($this->partner);
                if ($this->moderatorRole == 'moderator') {
                    $this->affiliationRewards->setModerator($this->moderator)
                        ->payModerator($this->partner, 'accept');
                }
                DB::commit();
            }
        } catch (InvalidModeratorException $exception) {
            throw $exception;
        } catch (ModeratorDistanceExceedException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    /**
     * @param $data
     * @throws InvalidModeratorException
     * @throws ModeratorDistanceExceedException
     * @throws \Throwable
     */
    public function reject($data)
    {
        try {
            if ($this->validateRequest($data)) {
                DB::beginTransaction();
                if (!isset($data['reject_reason'])) {
                    $data['reject_reason'] = 'Not Set';
                }
                $this->setPartnerData('rejected', $data['reject_reason']);
                if ($this->moderatorRole == 'moderator') {
                    $this->affiliationRewards->setModerator($this->moderator)
                        ->payModerator($this->partner, 'reject');
                }
                DB::commit();
            }
        } catch (InvalidModeratorException $exception) {
            throw $exception;
        } catch (ModeratorDistanceExceedException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    /**
     * @param $source
     * @param $partner
     * @throws ModeratorDistanceExceedException
     * @throws \Exception
     */
    private function validateLocation($source, $partner)
    {
        if (empty($partner->geo_informations)) {
            throw new \Exception('Partners Geo Information is not set yet');
        }

        $geo_info = json_decode($partner->geo_informations, true);
        $partner_radius = $geo_info['radius'] * 1000; // From KM to Meter
        $dist = self::calculateDistance($source, $geo_info);
        if ($dist > $partner_radius && $dist > $this->distanceThreshold) {
            throw new ModeratorDistanceExceedException();
        }
    }

    private function setPartnerData($status, $reason = null)
    {
        $this->partner->moderation_status = $status;
        if ($status == 'accepted') {
            if ($this->moderatorRole == 'moderator') {
                $this->partner->affiliation_cost = $this->affiliationRewards->getTotalCost();
            } else if ($this->moderatorRole == 'admin') {
                $this->partner->affiliation_cost = $this->affiliationRewards->getAffiliationCost();
            }
        } else {
            $this->partner->moderation_log = $reason;
            if ($this->moderatorRole == 'moderator') {
                $this->partner->affiliation_cost = $this->affiliationRewards->getModerationCost();
            } else if ($this->moderatorRole == 'admin') {
                $this->partner->affiliation_cost = 0;
            }
        }
        $this->partner->save();
    }

    public static function calculateDistance($source, $dest)
    {
        $distance = (new Distance(DistanceStrategy::$VINCENTY))->linear();
        return $distance->from($source)->to($dest)->distance();
        // return self::vincentyGreatCircleDistance(floatval($source['lat']), floatval($source['lng']), floatval($dest['lat']), floatval($dest['lng']));
    }
}
