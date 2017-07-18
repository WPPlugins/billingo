<?php

namespace Billingo\Plugin\Helper;

use Billingo\Plugin\Helper\BillingoObject;

class Invoice implements BillingoObject
{
    private $endpoint = 'invoices';
    private $billingo_request;

    public function __construct($billingo_request)
    {
        $this->billingo_request = $billingo_request;
    }

    public function get($params)
    {
        // TODO: test
        return $this->billingo_request->get($this->endpoint . '/' . join('/', $params));
    }

    public function create($data)
    {
        return $this->billingo_request->post($this->endpoint, $data);
    }
}
