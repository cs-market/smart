<?php

namespace Tygh\Addons\Helpdesk\Readers;

interface IReader
{
    /**
     * Gets header of an imported file.
     *
     * @return \Tygh\Common\OperationResult List of fields that imported items have
     */
    public function getMail();

    /**
     * Gets header of an imported file.
     *
     * @return \Tygh\Common\OperationResult List of fields that imported items have
     */
    public function setSettings($params);
}
