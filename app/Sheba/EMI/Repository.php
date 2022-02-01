<?php namespace Sheba\EMI;


use App\Models\Partner;
use App\Sheba\UserMigration\Modules;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Repository {
    /** @var Partner */
    private $partner;
    /** @var DataClient */
    private $client;
    private $offset;
    private $limit;
    private $query;

    /**
     * @param mixed $query
     * @return Repository
     */
    public function setQuery($query) {
        $this->query = $query;
        return $this;
    }

    /**
     * @param mixed $offset
     * @return Repository
     */
    public function setOffset($offset) {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param mixed $limit
     * @return Repository
     */
    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function __construct() { }

    /**
     * @param mixed $partner
     * @return Repository
     * @throws \Exception
     */
    public function setPartner(Partner $partner) {
        $this->partner = $partner;
        if ($partner->isMigrated(Modules::EXPENSE)) {
            $this->client = new AccountingDataClient($partner);
        }
        else {
            $this->client = new DataClient($partner);
        }
        return $this;
    }


    public function getRecent() {
        $list = collect($this->client->emiList(3));
        if ($this->partner->isMigrated(Modules::EXPENSE)) {
            $response = $list->flatMap(function ($item) {
                $res = [];
                foreach ($item['items'] as $i) {
                  $res[] = $i;
                }
                return $res;
            });
            return $response->take(3);
        }
        return $list->map(function ($item) {
            $item['partner'] = $this->partner;
            $nItem = new Item((array)$item);
            return $nItem->toShort();
        });
    }

    public function get() {
        $dItems = collect();
        $items = $list = collect($this->client->emiList());
        if ($this->query) {
            if (!$this->partner->isMigrated(Modules::EXPENSE)) {
                $items = $this->getShortItems($list);
            }
            $items = $items->filter(function ($item) {
                return preg_match("/{$this->query}/i", $item['customer_name']) || preg_match("/{$this->query}/i", $item['customer_mobile']);
            })->values();
            $items = $items->slice($this->offset)->take($this->limit)->values();
        } else {
            $list  = $list->slice($this->offset)->take($this->limit)->values();
            if (!$this->partner->isMigrated(Modules::EXPENSE)) {
                $items = $this->getShortItems($list);
            }
        }
        if ($this->partner->isMigrated(Modules::EXPENSE)) {
            return $items;
        }
        $dateWise = $items->groupBy('date')->toArray();
        foreach ($dateWise as $key => $dItem) {
            $dItems->push(['date' => $key, 'items' => $dItem]);
        }
        return $dItems;
    }

    private function getShortItems(Collection $list) {
        return $list->map(function ($item) {
            $item['partner'] = $this->partner;
            $nItem = new Item((array)$item);
            return $nItem->toShort();
        });
    }

    public function details($id) {
        $item = $this->client->getDetailEntry($id);
        if ($this->partner->isMigrated(Modules::EXPENSE)) {
            return $item;
        }
        return $item ? (new Item((array)$item))->setPartner($this->partner)->toDetails() : null;
    }
}
