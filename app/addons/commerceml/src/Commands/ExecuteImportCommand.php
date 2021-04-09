<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/


namespace Tygh\Addons\CommerceML\Commands;


/**
 * Class ExecuteImportCommand
 *
 * @package Tygh\Addons\CommerceML\Commands
 *
 * @see \Tygh\Addons\CommerceML\Commands\ExecuteImportCommandHandler
 */
class ExecuteImportCommand
{
    /**
     * @var int
     */
    private $import_id;

    /**
     * @var int
     */
    private $time_limit;

    /**
     * Creates command instance
     *
     * @param int $import_id  Import ID
     * @param int $time_limit Executing time limit
     *
     * @return \Tygh\Addons\CommerceML\Commands\ExecuteImportCommand
     */
    public static function create($import_id, $time_limit)
    {
        $self = new self();
        $self->import_id = (int) $import_id;
        $self->time_limit = (int) $time_limit;

        return $self;
    }

    /**
     * @return int
     */
    public function getImportId()
    {
        return $this->import_id;
    }

    /**
     * @return bool
     */
    public function hasTimeLimit()
    {
        return $this->time_limit > 0;
    }

    /**
     * @return int
     */
    public function getTimeLimit()
    {
        return $this->time_limit;
    }
}
