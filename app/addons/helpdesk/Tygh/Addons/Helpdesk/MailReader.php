<?php

namespace Tygh\Addons\Helpdesk;

use Tygh\Addons\Helpdesk\Readers\IReader;

class MailReader
{
    private $reader;

    public function __construct(IReader $reader)
    {
        $this->reader = $reader;
    }

    public function getMail() {
        return $this->reader->getMail();
    }

    public function setSettings($params) {
        return $this->reader->setSettings($params);
    }
}
