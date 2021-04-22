<?php


namespace Sheba\AccountingEntry\Repository;


use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
use GuzzleHttp\Client;
use ReflectionException;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\InvalidSourceException;
use Sheba\ModificationFields;
use Sheba\NeoBanking\Traits\ProtectedGetterTrait;
use Sheba\RequestIdentification;

class JournalCreateRepository
{
    use ModificationFields, ProtectedGetterTrait;

    /**
     * @var AccountingEntryClient
     */
    private   $client;
    private   $type;
    private   $typeId;
    protected $details = "";
    private   $source;
    protected $reference;
    private   $createdFrom;
    protected $amount;
    protected $debitAccountKey;
    protected $creditAccountKey;
    protected $entryAt;

    public function __construct()
    {
        $this->client = new AccountingEntryClient(new Client());
        $this->type   = UserType::PARTNER;

    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @throws ReflectionException
     */
    public function toData()
    {
        $data = $this->toArray();
        if (empty($this->source) || !is_object($this->source)) throw new InvalidSourceException();
        $data['entryAt']     = $data['entryAt'] ?: Carbon::now()->format('Y-m-d H:i:s');
        $data['sourceType']  = class_basename($this->source);
        $data['sourceId']    = $this->source->id;
        $data['createdFrom'] = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['details']     = $this->details . ", Auto Entry";
        return $data;
    }

    /**
     * @throws InvalidSourceException
     * @throws AccountingEntryServerError
     * @throws ReflectionException
     */
    public function store()
    {
        $data = $this->toData();
        return $this->client->setUserId($this->typeId)->setUserType($this->type)->post('api/journals/', $data);
    }


}
