<?php namespace Sheba\Pos\Repositories;

use App\Http\Controllers\TrainingVideoController;
use App\Models\PartnerPosSetting;
use Sheba\Dal\TrainingVideo\Contract as TrainingVideoRepository;
use Sheba\Repositories\BaseRepository;

class PosSettingRepository extends BaseRepository
{
    private $trainingVideoRepo;

    /**
     * PosSettingRepository constructor.
     * @param TrainingVideoRepository $trainingVideoRepo
     */
    public function __construct(TrainingVideoRepository $trainingVideoRepo)
    {
        parent::__construct();
        $this->trainingVideoRepo = $trainingVideoRepo;
    }

    /**
     * @param $data
     * @return PartnerPosSetting
     */
    public function save($data)
    {
        return PartnerPosSetting::create($this->withCreateModificationField($data));
    }

    private function formatDisplayData($data)
    {
        $data['printer_model'] = $data['printer_model'] ? : '';
        $data['printer_name'] = $data['printer_name'] ? : '';
        return $data;
    }

    public function getTrainingVideoData($data)
    {
        $this->formatDisplayData($data);
        $video = $this->trainingVideoRepo->getByScreen('printer_connectivity');
        $data['training_video'] = (new TrainingVideoController())->formatResponse($video);
        return $data;
    }
}
