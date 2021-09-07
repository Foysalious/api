<?php

namespace Sheba\AccountingEntry\Repository;


use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Repository\BaseRepository;
use Carbon\Carbon;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\InvalidSourceException;
use Sheba\AccountingEntry\Exceptions\KeyNotFoundException;
use Sheba\ModificationFields;
use Sheba\NeoBanking\Traits\ProtectedGetterTrait;
use Sheba\RequestIdentification;

class JournalCreateRepository extends BaseRepository
{
    use ModificationFields, ProtectedGetterTrait;

    /**
     * @var AccountingEntryClient
     */
    private   $type = UserType::PARTNER;
    private   $typeId;
    protected $details = "";
    private   $source;
    protected $reference;
    private   $createdFrom;
    protected $amount;
    protected $debitAccountKey;
    protected $creditAccountKey;
    protected $entryAt;
    protected $commission;
    private   $end_point = "api/journals/";
    private   $sourceType;
    private   $sourceId;
    private   $amountCleared;
    private   $note;

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param mixed $end_point
     * @return JournalCreateRepository
     */
    public function setEndPoint($end_point): JournalCreateRepository
    {
        $this->end_point = $end_point;
        return $this;
    }

    /**
     * @param mixed $typeId
     * @return JournalCreateRepository
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
        return $this;
    }

    /**
     * @param $commission
     * @return $this
     */
    public function setCommission($commission): JournalCreateRepository
    {
        $this->commission = $commission;
        return $this;
    }

    /**
     * @param string $details
     * @return JournalCreateRepository
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param mixed $source
     * @return JournalCreateRepository
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @param mixed $reference
     * @return JournalCreateRepository
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return JournalCreateRepository
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $debitAccountKey
     * @return JournalCreateRepository
     */
    public function setDebitAccountKey($debitAccountKey)
    {
        $this->debitAccountKey = $debitAccountKey;
        return $this;
    }

    /**
     * @param mixed $creditAccountKey
     * @return JournalCreateRepository
     */
    public function setCreditAccountKey($creditAccountKey)
    {
        $this->creditAccountKey = $creditAccountKey;
        return $this;
    }

    /**
     * @param mixed $entryAt
     * @return JournalCreateRepository
     */
    public function setEntryAt($entryAt)
    {
        $this->entryAt = $entryAt;
        return $this;
    }

    /**
     * @throws InvalidSourceException
     * @throws KeyNotFoundException
     */
    public function toData()
    {
        $data = $this->toArray();
        if (empty($this->source) || !is_object($this->source)) throw new InvalidSourceException();
        if (empty($this->creditAccountKey) || empty($this->debitAccountKey)) throw new KeyNotFoundException();
        $data['entryAt']     = $data['entryAt'] ?: Carbon::now()->format('Y-m-d H:i:s');
        $data['debit_account_key']  = $this->debitAccountKey;
        $data['credit_account_key'] = $this->creditAccountKey;
        $data['sourceType']  = class_basename($this->source);
        $data['commission']  = $this->commission;
        $data['sourceId']    = $this->source->id;
        $data['createdFrom'] = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['details']     = $this->details . ", Auto Entry";
        return $data;
    }

    /**
     * @return mixed
     * @throws AccountingEntryServerError
     * @throws InvalidSourceException
     * @throws KeyNotFoundException
     */
    public function store()
    {
        if(!$this->isMigratedToAccounting($this->typeId)) {
            return true;
        }
        $data = $this->toData();
        return $this->client->setUserId($this->typeId)->setUserType($this->type)->post($this->end_point, $data);
    }
}
