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


use Tygh\Addons\CommerceML\Dto\RepresentEntityDto;
use Tygh\Addons\CommerceML\Repository\ImportRepository;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Common\OperationResult;
use Tygh\Exceptions\DeveloperException;
use Tygh\Enum\SyncDataStatuses;

/**
 * Class AnalyzeImportCommandHandler
 *
 * @package Tygh\Addons\CommerceML\Commands
 */
class AnalyzeImportCommandHandler
{
    /**
     * @var \Tygh\Addons\CommerceML\Repository\ImportRepository
     */
    private $import_repository;

    /**
     * @var callable
     */
    private $import_storage_factory;

    /**
     * @var array<array-key, string>
     */
    private $mappable_entity_types;

    /**
     * AnalyzeImportCommandHandler constructor.
     *
     * @param \Tygh\Addons\CommerceML\Repository\ImportRepository $import_repository      Import repostiry
     * @param callable                                            $import_storage_factory Import storage factory
     * @param array<array-key, string>                            $mappable_entity_types  List of entity type
     */
    public function __construct(
        ImportRepository $import_repository,
        callable $import_storage_factory,
        array $mappable_entity_types
    ) {
        $this->import_repository = $import_repository;
        $this->import_storage_factory = $import_storage_factory;
        $this->mappable_entity_types = $mappable_entity_types;
    }

    /**
     * @param \Tygh\Addons\CommerceML\Commands\AnalyzeImportCommand $command Command instance
     *
     * @return \Tygh\Common\OperationResult
     */
    public function handle(AnalyzeImportCommand $command)
    {
        $import = $this->import_repository->findById($command->getImportId());

        if (!$import) {
            $result = new OperationResult(false);
            $result->addError('import', 'Import not found');
            return $result;
        }

        /** @var \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage */
        $import_storage = call_user_func($this->import_storage_factory, $import);

        if (!$import_storage instanceof ImportStorage) {
            throw new DeveloperException();
        }

        $this->setImportStatusToInProgress($import_storage);

        $result = new OperationResult(true);
        $result->setData($import_storage->getImport(), 'import');

        $time_start = time();

        foreach ($this->mappable_entity_types as $entity_type) {
            foreach ($import_storage->getQueue($entity_type, $this->getProcessId()) as $import_item) {
                if (!isset($import_item->entity) || !$import_item->entity instanceof RepresentEntityDto) {
                    continue;
                }

                $local_id = $import_storage->findEntityLocalId($import_item->entity);

                if ($local_id) {
                    continue;
                }

                $import_storage->mapEntityId($import_item->entity);

                if ($command->hasTimeLimit() && $command->getTimeLimit() < time() - $time_start) {
                    return $result;
                }
            }
        }

        $this->setImportStatusToSucessfullyFinished($import_storage);

        $import_storage->removeImport();

        return $result;
    }

    /**
     * Gets process ID
     *
     * @return string
     */
    private function getProcessId()
    {
        return uniqid('', true);
    }

    /**
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     */
    private function setImportStatusToInProgress(ImportStorage $import_storage)
    {
        $import_storage->getImport()->status = SyncDataStatuses::STATUS_PROGRESS;
        $import_storage->saveImport();
    }

    /**
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     */
    private function setImportStatusToSucessfullyFinished(ImportStorage $import_storage)
    {
        $import_storage->getImport()->status = SyncDataStatuses::STATUS_SUCCESS;
        $import_storage->saveImport();
    }
}
