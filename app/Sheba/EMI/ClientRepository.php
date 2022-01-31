<?php namespace Sheba\EMI;


abstract class ClientRepository {
    abstract public function emiList();
    abstract public function getDetailEntry($id);
}
