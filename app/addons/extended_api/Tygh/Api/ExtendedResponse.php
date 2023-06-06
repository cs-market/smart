<?php

namespace Tygh\Api;

class ExtendedResponse extends Response
{
    public function getBody()
    {
        return $this->body;
    }
    public function setBody($body)
    {
        $this->body = $body;
    }
}
