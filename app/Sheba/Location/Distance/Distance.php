<?php namespace Sheba\Location\Distance;

use Sheba\Location\Distance\Strategies\Cosine;
use Sheba\Location\Distance\Strategies\EllipsoidalPlane;
use Sheba\Location\Distance\Strategies\GoogleDistanceMatrix;
use Sheba\Location\Distance\Strategies\Haversine;
use Sheba\Location\Distance\Strategies\SphericalPlane;
use Sheba\Location\Distance\Strategies\Vincenty;

class Distance
{
    /** @var DistanceCalculator */
    private $strategy;

    public function __construct($strategy)
    {
        $this->strategy = $this->getStrategy($strategy);
    }

    public function linear()
    {
        return $this->strategy;
    }

    public function matrix()
    {
        return new DistanceMatrixCalculator($this->strategy);
    }

    /**
     * @param $strategy
     * @return bool
     */
    private function isValidStrategy($strategy)
    {
        return in_array($strategy, (new \ReflectionClass(DistanceStrategy::class))->getStaticProperties());
    }

    /**
     * @param $strategy
     * @return DistanceCalculator
     */
    private function getStrategy($strategy)
    {
        if(!$this->isValidStrategy($strategy)) throw new \InvalidArgumentException('Invalid Strategy.');

        switch ($strategy) {
            case 'haversine': return new Haversine();
            case 'cosine': return new Cosine();
            case 'ellipsoidal_plane': return new EllipsoidalPlane();
            case 'spherical_plane': return new SphericalPlane();
            case 'vincenty': return new Vincenty();
            case 'google_distance_matrix': return new GoogleDistanceMatrix();
        }
    }
}