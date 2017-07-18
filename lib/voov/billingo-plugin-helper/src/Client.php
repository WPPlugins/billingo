<?php

namespace Billingo\Plugin\Helper;

use Billingo\Plugin\Helper\BillingoObject;

class Client implements BillingoObject
{
    private $endpoint = 'clients';
    private $billingo_request;

    public function __construct($billingo_request)
    {
        $this->billingo_request = $billingo_request;
    }

    public function get($params)
    {
        return $this->billingo_request->get($this->endpoint . '/' . join('/', $params));
    }

    public function create($data)
    {
        return $this->billingo_request->post($this->endpoint, $data);
    }
}
