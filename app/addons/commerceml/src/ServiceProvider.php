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


namespace Tygh\Addons\CommerceML;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Addons\CommerceML\Commands\RemoveImportCommandHandler;
use Tygh\Addons\CommerceML\Commands\AuthCommandHandler;
use Tygh\Addons\CommerceML\Commands\CleanUpFilesDirCommandHandler;
use Tygh\Addons\CommerceML\Commands\CreateImportCommandHandler;
use Tygh\Addons\CommerceML\Commands\ExecuteImportCommandHandler;
use Tygh\Addons\CommerceML\Commands\UnzipImportFileCommandHandler;
use Tygh\Addons\CommerceML\Commands\UploadImportFileCommandHandler;
use Tygh\Addons\CommerceML\Convertors\CategoryConvertor;
use Tygh\Addons\CommerceML\Convertors\PriceTypeConvertor;
use Tygh\Addons\CommerceML\Convertors\ProductConvertor;
use Tygh\Addons\CommerceML\Convertors\ProductFeatureConvertor;
use Tygh\Addons\CommerceML\Dto\ImportDto;
use Tygh\Addons\CommerceML\Dto\PriceTypeDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Importers\CategoryImporter;
use Tygh\Addons\CommerceML\Importers\ProductFeatureImporter;
use Tygh\Addons\CommerceML\Importers\ProductImporter;
use Tygh\Addons\CommerceML\Importers\ProductVariationAsProductImporter;
use Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository;
use Tygh\Addons\CommerceML\Repository\ImportEntityRepository;
use Tygh\Addons\CommerceML\Repository\ImportRemovedEntityRepository;
use Tygh\Addons\CommerceML\Repository\ImportRepository;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Storages\ProductStorage;
use Tygh\Addons\CommerceML\Tools\Logger;
use Tygh\Addons\CommerceML\Xml\XmlParser;
use Tygh\Addons\ProductVariations\ServiceProvider as VariationsServiceProvider;
use Tygh\Bootstrap as CoreBootstrap;
use Tygh\Enum\YesNo;
use Tygh\Tygh;
use Tygh\Registry;

/**
 * Class ServiceProvider is intended to register services and components of the "CommerceML" add-on to the application
 * container.
 *
 * @package Tygh\Addons\CommerceML
 *
 * @phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $app['addons.commerceml.repository.import_repository'] = static function (Container $app) {
            return new ImportRepository($app['db']);
        };

        $app['addons.commerceml.repository.import_entity_repository'] = static function (Container $app) {
            return new ImportEntityRepository($app['db']);
        };

        $app['addons.commerceml.repository.import_entity_map_repository'] = static function (Container $app) {
            return new ImportEntityMapRepository($app['db']);
        };

        $app['addons.commerceml.repository.import_removed_entity_repository'] = static function (Container $app) {
            return new ImportRemovedEntityRepository($app['db']);
        };

        $app['addons.commerceml.convertors.product_convertor'] = static function () {
            return new ProductConvertor(
                TranslatableValueDto::create(__('commerceml.variation_default_product_feature_name')),
                TranslatableValueDto::create(__('commerceml.brand_default_product_feature_name')),
                ProductFeatureDto::BRAND_EXTERNAL_ID
            );
        };

        $app['addons.commerceml.storages.product_storage'] = static function () {
            $lang_code = Registry::get('addons.commerceml.cml_default_lang');

            if (empty($lang_code)) {
                $lang_code = Registry::get('settings.Appearance.backend_default_language');
            }

            return new ProductStorage($lang_code);
        };

        $app['addons.commerceml.convertors.product_feature_convertor'] = static function () {
            return new ProductFeatureConvertor();
        };

        $app['addons.commerceml.convertors.price_type_convertor'] = static function () {
            return new PriceTypeConvertor();
        };

        $app['addons.commerceml.convertors.category_confertor'] = static function () {
            return new CategoryConvertor(self::getProductFeatureConvertor());
        };

        $app['addons.commerceml.importers.category_importer'] = static function (Container $app) {
            return new CategoryImporter($app['addons.commerceml.storages.product_storage']);
        };

        $app['addons.commerceml.importers.product_feature_importer'] = static function (Container $app) {
            return new ProductFeatureImporter($app['addons.commerceml.storages.product_storage']);
        };

        $app['addons.commerceml.importers.product_importer'] = static function (Container $app) {
            return new ProductImporter(
                $app['addons.commerceml.importers.category_importer'],
                $app['addons.commerceml.importers.product_feature_importer'],
                $app['addons.commerceml.storages.product_storage']
            );
        };

        $app['addons.commerceml.importers.product_variation_importer'] = static function (Container $app) {
            return new ProductVariationAsProductImporter(
                $app['addons.commerceml.importers.product_importer'],
                $app['addons.commerceml.importers.product_feature_importer'],
                VariationsServiceProvider::getGroupRepository(),
                VariationsServiceProvider::getService(),
                $app['addons.commerceml.storages.product_storage']
            );
        };

        $app['addons.commerceml.xml.xml_parser'] = static function () {
            return new XmlParser();
        };

        $app['addons.commerceml.import_storage_factory'] = static function () {
            return static function (ImportDto $import) {
                return new ImportStorage(
                    $import,
                    self::getImportRepository(),
                    self::getImportEntityRepository(),
                    self::getImportEntityMapRepository(),
                    self::getImportRemovedEntityRepository(),
                    array_keys(self::getManualMappableEntitiesSchema()),
                    self::getCatalogSettings($import->company_id)
                );
            };
        };

        $app['addons.commerceml.command_bus'] = static function () {
            return new CommandBus((array) fn_get_schema('cml', 'commands'));
        };

        $app['addons.commerceml.commands.create_import_command_handler'] = static function () {
            return new CreateImportCommandHandler(
                self::getImportStorageFactory(),
                self::getXmlParser(),
                (array) fn_get_schema('cml', 'callbacks')
            );
        };

        $app['addons.commerceml.commands.auth_command_handler'] = static function (Container $app) {
            return new AuthCommandHandler(
                Registry::get('runtime.forced_company_id'),
                fn_allowed_for('ULTIMATE') && Registry::get('runtime.simple_ultimate')
            );
        };

        $app['addons.commerceml.commands.upload_import_file_command_handler'] = static function () {
            return new UploadImportFileCommandHandler();
        };

        $app['addons.commerceml.commands.unzip_import_file_command_handler'] = static function (Container $app) {
            return new UnzipImportFileCommandHandler(
                $app['archiver']
            );
        };

        $app['addons.commerceml.commands.execute_import_command_handler'] = static function (Container $app) {
            return new ExecuteImportCommandHandler(
                self::getImportRepository(),
                $app['addons.commerceml.importers.product_importer'],
                $app['addons.commerceml.importers.product_variation_importer'],
                self::getImportStorageFactory(),
                self::getLogger()
            );
        };

        $app['addons.commerceml.commands.remove_import_command_handler'] = static function () {
            return new RemoveImportCommandHandler(
                self::getImportRepository(),
                self::getImportStorageFactory()
            );
        };

        $app['addons.commerceml.commands.clean_up_files_dir_command_handler'] = static function () {
            return new CleanUpFilesDirCommandHandler();
        };

        $app['addons.commerceml.log_file'] = 'commerceml.log';
        $app['addons.commerceml.log_file_path'] = static function () {
            return sprintf('%s/exim/commerceml.log', rtrim(fn_get_files_dir_path(), '/'));
        };

        $app['addons.commerceml.logger'] = static function () use ($app) {
            return new Logger(
                $app['addons.commerceml.log_file_path'],
                DEFAULT_FILE_PERMISSIONS,
                DEFAULT_DIR_PERMISSIONS,
                Registry::ifGet('config.commerceml.max_log_file_size', 10240), //10mb
                Registry::ifGet('config.commerceml.max_log_files', 10)
            );
        };
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportRepository
     */
    public static function getImportRepository()
    {
        return Tygh::$app['addons.commerceml.repository.import_repository'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportEntityRepository
     */
    public static function getImportEntityRepository()
    {
        return Tygh::$app['addons.commerceml.repository.import_entity_repository'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository
     */
    public static function getImportEntityMapRepository()
    {
        return Tygh::$app['addons.commerceml.repository.import_entity_map_repository'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportRemovedEntityRepository
     */
    public static function getImportRemovedEntityRepository()
    {
        return Tygh::$app['addons.commerceml.repository.import_removed_entity_repository'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Convertors\ProductConvertor
     */
    public static function getProductConvetor()
    {
        return Tygh::$app['addons.commerceml.convertors.product_convertor'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Convertors\ProductFeatureConvertor
     */
    public static function getProductFeatureConvertor()
    {
        return Tygh::$app['addons.commerceml.convertors.product_feature_convertor'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Convertors\PriceTypeConvertor
     */
    public static function getPriceTypeConvertor()
    {
        return Tygh::$app['addons.commerceml.convertors.price_type_convertor'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Convertors\CategoryConvertor
     */
    public static function getCategoryConvertor()
    {
        return Tygh::$app['addons.commerceml.convertors.category_confertor'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Xml\XmlParser
     */
    public static function getXmlParser()
    {
        return Tygh::$app['addons.commerceml.xml.xml_parser'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\CommandBus
     */
    public static function getCommandBus()
    {
        return Tygh::$app['addons.commerceml.command_bus'];
    }

    /**
     * @return callable
     */
    public static function getImportStorageFactory()
    {
        return Tygh::$app['addons.commerceml.import_storage_factory'] ;
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\CreateImportCommandHandler
     */
    public static function getCreateImportCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.create_import_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\AuthCommandHandler
     */
    public static function getAuthCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.auth_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\UploadImportFileCommandHandler
     */
    public static function getUploadImportFileCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.upload_import_file_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\UnzipImportFileCommandHandler
     */
    public static function getUnzipImportFileCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.unzip_import_file_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\ExecuteImportCommandHandler
     */
    public static function getExecuteImportCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.execute_import_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\RemoveImportCommandHandler
     */
    public static function getRemoveImportCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.remove_import_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Commands\CleanUpFilesDirCommandHandler
     */
    public static function getCleanUpFilesDirCommandHandler()
    {
        return Tygh::$app['addons.commerceml.commands.clean_up_files_dir_command_handler'];
    }

    /**
     * @return \Tygh\Addons\CommerceML\Tools\Logger
     */
    public static function getLogger()
    {
        return Tygh::$app['addons.commerceml.logger'];
    }

    /**
     * Gets schema for manual mappable entities
     *
     * @return array<string, array{items_provider: callable}>
     */
    public static function getManualMappableEntitiesSchema()
    {
        return (array) fn_get_schema('cml', 'mappable');
    }

    /**
     * Checks if company requires to manual mapping entities
     *
     * @param int $company_id Company ID
     *
     * @return bool
     */
    public static function isManualMappingRequired($company_id)
    {
        $map_repository = self::getImportEntityMapRepository();

        list($records) = $map_repository->findAll([
            'company_id'   => $company_id,
            'entity_type'  => PriceTypeDto::REPRESENT_ENTITY_TYPE,
            'has_local_id' => true,
            'limit'        => 1
        ]);

        return empty($records);
    }

    /**
     * Checks if catalog import enabled
     *
     * @param int|null $company_id Company ID
     *
     * @return bool
     */
    public static function isCatalogImportEnabled($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $settings = self::getCatalogSettings($company_id);

        return isset($settings['import_mode']) ? $settings['import_mode'] !== 'none' : true;
    }

    /**
     * Checks if offers import enabled
     *
     * @param int|null $company_id Company ID
     *
     * @return bool
     */
    public static function isOffersImportEnabled($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $settings = self::getCatalogSettings($company_id);

        /** @var bool $settings['allow_import_offers'] */
        return isset($settings['allow_import_offers']) ? $settings['allow_import_offers'] : true;
    }

    /**
     * Gets catalog settings
     *
     * @param int|null $company_id Company ID
     *
     * @return array<string, int|string|bool|array>
     */
    public static function getCatalogSettings($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $result = [];
        $settings = fn_get_sync_data_settings('commerceml_catalog', $company_id);

        $bool_settings = [
            'allow_import_offers',
            'hide_out_of_stock_products',
            'allow_import_categories',
            'allow_import_features',
            'allow_matching_product_by_product_code',
            'allow_matching_category_by_name',
            'import_images_as_additional',
            'convertor_display_weight',
            'convertor_display_free_shipping',
        ];

        $list_settings = [
            'convertor_weight_property_source_list',
            'convertor_free_shipping_property_source_list',
            'convertor_shipping_cost_property_source_list',
            'convertor_number_of_items_property_source_list',
            'convertor_box_length_property_source_list',
            'convertor_box_width_property_source_list',
            'convertor_box_height_property_source_list',
            'convertor_property_blocklist',
            'convertor_property_allowlist',
        ];

        if (isset($settings['catalog'])) {
            foreach ($settings['catalog'] as $key => $value) {
                if (in_array($key, $bool_settings, true)) {
                    $value = YesNo::toBool($value);
                } elseif (in_array($key, $list_settings, true)) {
                    $value = fn_explode("\n", $value);
                    $value = empty($value[0]) ? [] : $value;
                }

                $result[$key] = $value;
            }
        }

        $result['lang_codes'] = array_keys(Tygh::$app['languages']);
        $result['upload_dir_path'] = self::getUploadDirPath();
        $result['allow_negative_amount'] = YesNo::toBool(Registry::get('settings.General.allow_negative_amount'));

        if ($company_id && fn_allowed_for('MULTIVENDOR')) {
            $result['allow_import_categories'] = false;

            if (!YesNo::toBool(Registry::get('settings.Vendors.allow_vendor_manage_features'))) {
                $result['allow_import_features'] = false;
            }
        } else {
            if (!isset($result['allow_import_categories'])) {
                $result['allow_import_categories'] = true;
            }

            if (!isset($result['allow_import_features'])) {
                $result['allow_import_features'] = true;
            }
        }

        return $result;
    }

    /**
     * Gets upload directory path
     *
     * @return string
     */
    public static function getUploadDirPath()
    {
        return sprintf('%s/exim/1C/', rtrim(fn_get_files_dir_path(), '/'));
    }

    /**
     * Gets file limit for uploading
     *
     * @return int
     */
    public static function getUploadFileLimit()
    {
        $upload_max_filesize = CoreBootstrap::getIniParam('upload_max_filesize', true);
        $post_max_size = CoreBootstrap::getIniParam('post_max_size', true);

        $file_limit = min(
            fn_return_bytes($upload_max_filesize),
            fn_return_bytes($post_max_size)
        );

        $config_file_limit = Registry::ifGet('config.commerceml.file_limit', false);

        $file_limit = empty($config_file_limit)
            ? $file_limit
            : min(fn_return_bytes($config_file_limit), $file_limit);

        return $file_limit;
    }

    /**
     * Checks if zip allowed
     *
     * @return bool
     */
    public static function isZipAllowed()
    {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        return YesNo::toBool(Registry::ifGet('config.commerceml.allow_zip', true));
    }

    /**
     * Gets last synchronization info
     *
     * @param int $company_id Company identifier
     *
     * @return array{status: string, last_sync_timestamp: int, log_file_url: string, status_code?: string}
     */
    public static function getLastSyncInfo($company_id = null)
    {
        if ($company_id === null) {
            $company_id = fn_get_runtime_company_id();
        }

        $result = [
            'last_sync_timestamp' => 0,
            'status'              => '',
            'log_file_url'        => '',
            'log_file_name'       => (string) fn_basename(Tygh::$app['addons.commerceml.log_file_path']),
        ];

        $last_sync = db_get_row('SELECT * FROM ?:commerceml_imports WHERE company_id = ?i ORDER BY updated_at DESC LIMIT 1', $company_id);

        if (empty($last_sync)) {
            return $result;
        }

        $result['status'] = __('commerceml.last_status.' . $last_sync['status']);
        $result['status_code'] = (string) $last_sync['status'];
        $result['last_sync_timestamp'] = (int) $last_sync['updated_at'];
        $result['log_file_url']  = fn_url('commerceml.get_log?company_id=' . $company_id);

        return $result;
    }
}
