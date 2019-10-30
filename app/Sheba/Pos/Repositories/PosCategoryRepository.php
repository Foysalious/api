<?php namespace Sheba\Pos\Repositories;

use App\Models\PosCategory;
use Sheba\Pos\Repositories\Interfaces\PosCategoryRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class PosCategoryRepository extends BaseRepository implements PosCategoryRepositoryInterface
{
    public function __construct(PosCategory $posCategory)
    {
        parent::__construct();
        $this->setModel($posCategory);
    }
}