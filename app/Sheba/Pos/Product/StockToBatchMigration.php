<?php namespace Sheba\Pos\Product;


use Sheba\Dal\PartnerPosService\PartnerPosServiceRepositoryInterface;
use Sheba\Dal\PartnerPosServiceBatch\PartnerPosServiceBatchRepositoryInterface;

class StockToBatchMigration
{
    /** @var int */
    private $partnerId;

    /**
     * @param int $partnerId
     * @return StockToBatchMigration
     */
    public function setPartnerId(int $partnerId): StockToBatchMigration
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    public function migrateStock()
    {
        /** @var PartnerPosServiceRepositoryInterface $partnerPosServiceRepository */
        $partnerPosServiceRepository = app(PartnerPosServiceRepositoryInterface::class);
        $partnerPosServiceData = $partnerPosServiceRepository->builder()->select('id AS partner_pos_service_id', 'cost', 'stock',
            'deleted_at', 'created_by', 'created_by_name', 'updated_by', 'updated_by_name', 'created_at', 'updated_at')
            ->where('partner_id', $this->partnerId)->whereDoesntHave('batches')->withTrashed()->get()->toArray();
        /** @var PartnerPosServiceBatchRepositoryInterface $partnerPosServiceBatchRepository */
        $partnerPosServiceBatchRepository = app(PartnerPosServiceBatchRepositoryInterface::class);
        $partnerPosServiceBatchRepository->insert($partnerPosServiceData);
    }
}