<?php

namespace Billingo\Plugin\Helper;

interface BillingoObject
{
    public function get($data);
    public function create($params);
}
