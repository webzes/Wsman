<?php

namespace c0py\Wsman\Interfaces;

interface SessionInterface
{
    public function get($query);

    public function enumerate($query, $filter, $dialect, $flags);
}
