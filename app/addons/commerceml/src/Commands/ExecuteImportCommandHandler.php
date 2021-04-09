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


use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Importers\ProductImporter;
use Tygh\Addons\CommerceML\Importers\ProductVariationAsProductImporter;
use Tygh\Addons\CommerceML\Repository\ImportRepository;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Tools\Logger;
use Tygh\Common\OperationResult;
use Tygh\Enum\SyncDataStatuses;
use Tygh\Exceptions\DeveloperException;

/**
 * Class ExecuteImportCommandHandler
 *
 * @package Tygh\Addons\CommerceML\Commands
 */
class ExecuteImportCommandHandler
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
     * @var \Tygh\Addons\CommerceML\Importers\ProductImporter
     */
    private $product_importer;

    /**
     * @var \Tygh\Addons\CommerceML\Importers\ProductVariationAsProductImporter
     */
    private $product_variations_importer;

    /**
     * @var \Tygh\Addons\CommerceML\Tools\Logger
     */
    private $logger;

    /**
     * ExecuteImportCommandHandler constructor.
     *
     * @param \Tygh\Addons\CommerceML\Repository\ImportRepository                 $import_repository           Import repostiry
     * @param \Tygh\Addons\CommerceML\Importers\ProductImporter                   $product_importer            Product importer instnace
     * @param \Tygh\Addons\CommerceML\Importers\ProductVariationAsProductImporter $product_variations_importer Product importer instnace
     * @param callable                                                            $import_storage_factory      Import storage factory
     * @param \Tygh\Addons\CommerceML\Tools\Logger                                $logger                      Import process logger
     */
    public function __construct(
        ImportRepository $import_repository,
        ProductImporter $product_importer,
        ProductVariationAsProductImporter $product_variations_importer,
        callable $import_storage_factory,
        Logger $logger
    ) {
        $this->import_repository = $import_repository;
        $this->product_importer = $product_importer;
        $this->product_variations_importer = $product_variations_importer;
        $this->import_storage_factory = $import_storage_factory;
        $this->logger = $logger;
    }

    /**
     * @param \Tygh\Addons\CommerceML\Commands\ExecuteImportCommand $command Command instance
     *
     * @return \Tygh\Common\OperationResult
     */
    public function handle(ExecuteImportCommand $command)
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

        foreach ($import_storage->getQueue(ProductDto::REPRESENT_ENTITY_TYPE, $this->getProcessId()) as $import_item) {
            if (!isset($import_item->entity) || !$import_item->entity instanceof ProductDto) {
                continue;
            }

            $this->logger->info(__('commerceml.import.message.start_import_product', [
                '[id]' => $import_item->entity->id->getId()
            ]));

            if ($import_item->entity->is_variation) {
                $import_result = $this->product_variations_importer->import($import_item->entity, $import_storage);
            } else {
                $import_result = $this->product_importer->import($import_item->entity, $import_storage);
            }

            $this->logger->logResult($import_result);
            $this->logger->info(__('commerceml.import.message.end_import_product', [
                '[id]' => $import_item->entity->id->getId()
            ]));

            if ($command->hasTimeLimit() && $command->getTimeLimit() < time() - $time_start) {
                return $result;
            }
        }

        $this->setImportStatusToSucessfullyFinished($import_storage);

        $import_storage->removeAllEntites();

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
