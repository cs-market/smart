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


namespace Tygh\Addons\ProductVariations\Product;


use Tygh\Database\Connection;
use Tygh\Enum\ProductTracking;

/**
 * Provides methods for working with product variations.
 *
 * @package Tygh\Addons\ProductVariations
 */
class Manager
{
    /** Configurable product type */
    const PRODUCT_TYPE_CONFIGURABLE = 'C';

    /** Product type variation */
    const PRODUCT_TYPE_VARIATION = 'V';

    /** Simple product type */
    const PRODUCT_TYPE_SIMPLE = 'P';

    /** The min amount that assumes that product is in stock */
    const PRODUCT_IN_STOCK_AMOUNT = 1;

    /** @var Connection */
    protected $db;

    /** @var array */
    protected $products_data_cache = array();

    /** @var array|null */
    protected $type_schemas;

    /** @var array */
    protected $types_cache = array();

    /** @var array */
    protected $variations_ids_cache = array();

    /** @var bool Inventory tracking mode (settings.General.inventory_tracking) */
    protected $inventory_tracking;

    /** @var bool Whether to show out of stock products (settings.General.show_out_of_stock_products) */
    protected $show_out_of_stock_products;

    /** @var array  */
    protected $product_variation_codes_cache = array();

    /**
     * Manager constructor.
     *
     * @param Connection $db                            Instance of database connection.
     * @param string     $inventory_tracking            Inventory tracking mode (settings.General.inventory_tracking)
     * @param string     $show_out_of_stock_products    Whether to show out of stock products (settings.General.show_out_of_stock_products)
     */
    public function __construct(Connection $db, $inventory_tracking, $show_out_of_stock_products)
    {
        $this->db = $db;
        $this->inventory_tracking = $inventory_tracking === 'Y';
        $this->show_out_of_stock_products = $show_out_of_stock_products === 'Y';
    }

    /**
     * Gets product field value.
     *
     * @param int       $product_id    Product identifier
     * @param string    $field         Product db field
     * @param mixed     $default       Default values if product is undefined
     * @param boolean   $ignore_cache  Ignore the cache when getting the value of the field
     *
     * @return mixed
     */
    public function getProductFieldValue($product_id, $field, $default = null, $ignore_cache = false)
    {
        if ($ignore_cache || !array_key_exists($product_id, $this->products_data_cache)) {
            $this->products_data_cache[$product_id] = $this->db->getRow(
                'SELECT * FROM ?:products WHERE product_id = ?i',
                $product_id
            );
        }

        return isset($this->products_data_cache[$product_id][$field])
            ? $this->products_data_cache[$product_id][$field]
            : $default;
    }

    /**
     * Gets product variation options.
     *
     * @param int $product_id Product identifier.
     *
     * @return array
     */
    public function getProductVariationOptionsValue($product_id)
    {
        $value = $this->getProductFieldValue($product_id, 'variation_options');
        $value = $this->decodeVariationOptions($value);

        if (!is_array($value)) {
            $value = array();
        }

        return $value;
    }

    /**
     * Gets product types schema.
     *
     * @return array
     */
    public function getProductTypeSchemas()
    {
        if ($this->type_schemas === null) {
            $this->type_schemas = fn_get_schema('product_variations', 'product_types');
        }

        return $this->type_schemas;
    }

    /**
     * Gets product type schema by product type.
     *
     * @param string $type Product type
     * @return array
     */
    public function getProductTypeSchema($type)
    {
        $schemas = $this->getProductTypeSchemas();

        return isset($schemas[$type]) ? $schemas[$type] : array();
    }

    /**
     * Gets product types name.
     *
     * @param array|null $types Filter by product types.
     *
     * @return array
     */
    public function getProductTypeNames(array $types = null)
    {
        $names = array();
        $schemas = $this->getProductTypeSchemas();

        foreach ($schemas as $type => $schema) {
            if ($types === null || in_array($type, $types, true)) {
                $names[$type] = $schema['name'];
            }
        }

        return $names;
    }

    /**
     * Gets list of creatable product types.
     *
     * @return array
     */
    public function getCreatableProductTypes()
    {
        $types = array();
        $schemas = $this->getProductTypeSchemas();

        foreach ($schemas as $type => $schema) {
            if (!empty($schema['creatable'])) {
                $types[] = $type;
            }
        }

        return $types;
    }

    /**
     * Checks if product type is defined.
     *
     * @param string $type Product type (P,S,C)
     *
     * @return bool
     */
    public function isProductTypeExists($type)
    {
        return $this->getProductTypeSchema($type) !== array();
    }

    /**
     * Gets product type instance.
     *
     * @param string $type Product type (P,S,C)
     *
     * @return Type
     */
    public function getProductTypeInstance($type)
    {
        if (!$this->isProductTypeExists($type)) {
            $type = self::PRODUCT_TYPE_SIMPLE;
        }

        if (!isset($this->types_cache[$type])) {
            $this->types_cache[$type] = new Type($this->getProductTypeSchema($type));
        }

        return $this->types_cache[$type];
    }

    /**
     * Gets product type instance by product identifier.
     *
     * @param int $product_id Product identifier
     *
     * @return Type
     */
    public function getProductTypeInstanceByProductId($product_id)
    {
        return $this->getProductTypeInstance($this->getProductFieldValue($product_id, 'product_type'));
    }

    /**
     * Generates variation code by product identifier and selected options.
     *
     * @param int   $product_id         Product identifier
     * @param array $selected_options   List of selected options as option_id => variant_id
     *
     * @return string
     */
    public function getVariationCode($product_id, $selected_options)
    {
        sort($selected_options);
        return $product_id . '_' . implode('_', $selected_options);
    }

    /**
     * Finds product variation by selected options.
     *
     * @param int   $product_id         Product identifier
     * @param array $selected_options   List of selected options as option_id => variant_id
     *
     * @return bool|int
     */
    public function getVariationId($product_id, array $selected_options)
    {
        if (empty($selected_options)) {
            return false;
        }

        asort($selected_options);
        $key = md5($product_id . @serialize($selected_options));

        if (array_key_exists($key, $this->variations_ids_cache)) {
            $this->variations_ids_cache[$key];
        }

        $variation_option_ids = $this->getProductVariationOptionsValue($product_id);

        foreach ($selected_options as $option_id => $variant_id) {
            if (!in_array($option_id, $variation_option_ids)) {
                unset($selected_options[$option_id]);
            }
        }

        $variation_code = $this->getVariationCode($product_id, $selected_options);
        $variation_id = (int) $this->db->getField('SELECT product_id FROM ?:products WHERE variation_code = ?s', $variation_code);

        $this->variations_ids_cache[$variation_code] = $variation_id;

        return $variation_id;
    }

    /**
     * Retrieves list of variant identifiers from variation code.
     *
     * @param string $code Variation code
     * @return array
     */
    public function getVariantIdsByVariationCode($code)
    {
        $variation_ids = explode('_', $code);
        array_shift($variation_ids);

        return $variation_ids;
    }

    /**
     * Gets selected options by variation code.
     *
     * @param string        $code               Variation code
     * @param array|null    $product_options    List of product options
     *
     * @return array
     */
    public function getSelectedOptionsByVariationCode($code, array $product_options = null)
    {
        $variation_ids = $this->getVariantIdsByVariationCode($code);
        $selected_options = array();

        if ($product_options === null) {
            $selected_options = $this->db->getSingleHash(
                'SELECT option_id, variant_id FROM ?:product_option_variants WHERE variant_id IN (?n)',
                array('option_id', 'variant_id'),
                $variation_ids
            );
        } else {
            foreach ($product_options as $option) {
                foreach ($variation_ids as $key => $variant_id) {
                    if (isset($option['variants'][$variant_id])) {
                        $selected_options[$option['option_id']] = $variant_id;
                        unset($variation_ids[$key]);
                        break;
                    }
                }
            }
        }

        return $selected_options;
    }

    /**
     * Actualizes amount for configurable products.
     *
     * @param int[] $product_ids Configurable product identifiers.
     */
    public function actualizeConfigurableProductAmount($product_ids)
    {
        $product_ids = (array) $product_ids;

        $products_amounts = $this->db->getSingleHash(
            'SELECT parent_product_id, MAX(amount) AS amount FROM ?:products'
            . ' WHERE parent_product_id IN (?n) AND status = ?s'
            . ' GROUP BY parent_product_id',
            array('parent_product_id', 'amount'),
            $product_ids, 'A'
        );

        foreach ($product_ids as $product_id) {
            $amount = isset($products_amounts[$product_id]) ? $products_amounts[$product_id] : 0;

            $this->db->query(
                'UPDATE ?:products SET amount = ?i WHERE product_id = ?i AND product_type = ?s',
                $amount, $product_id, self::PRODUCT_TYPE_CONFIGURABLE
            );
        }
    }

    /**
     * Fetches all variation codes by parent product id
     *
     * @param int  $product_id   Product id
     * @param bool $update_cache Updating cache flag
     *
     * @return array
     */
    public function getProductVariationCodes($product_id, $update_cache = false)
    {
        if (!isset($this->product_variation_codes_cache[$product_id]) || $update_cache) {
            $this->product_variation_codes_cache[$product_id] = $this->db->getSingleHash(
                'SELECT variation_code FROM ?:products WHERE parent_product_id = ?i',
                array('variation_code', 'variation_code'),
                $product_id
            );
        }

        return $this->product_variation_codes_cache[$product_id];
    }

    /**
     * @param int   $product_id     Product identifier
     * @param bool  $update_cache   Update cache flag
     *
     * @return bool
     */
    public function hasProductVariations($product_id, $update_cache = false)
    {
        $codes = $this->getProductVariationCodes($product_id, $update_cache);

        return !empty($codes);
    }

    /**
     * @param int    $product_id     Product identifier
     * @param string $variation_code Variation code
     * @param bool   $update_cache   Update cache flag
     *
     * @return bool
     */
    public function existsProductVariation($product_id, $variation_code, $update_cache = false)
    {
        $codes = $this->getProductVariationCodes($product_id, $update_cache);

        return isset($codes[$variation_code]);
    }

    /**
     * @return bool
     */
    public function isInventoryTracking()
    {
        return $this->inventory_tracking;
    }

    /**
     * @return bool
     */
    public function isShowOutOfStockProducts()
    {
        return $this->show_out_of_stock_products;
    }

    /**
     * Extracts selected options of product variation that passed as argument
     *
     * @param array $product Array with product data
     *
     * @return array|mixed
     */
    public function getProductVariationOptions($product)
    {
        $variation_options = array();

        if (isset($product['variation_options'])) {

            if (is_array($product['variation_options'])) {
                $variation_options = $product['variation_options'];
            } else {
                $variation_options = $this->decodeVariationOptions($product['variation_options']);
            }
        }

        return $variation_options;
    }

    /**
     * Gets product variation identifiers
     *
     * @param int $product_id Product identifier.
     *
     * @return array Product variation identifier.
     */
    public function getProductVariations($product_id)
    {
        $product_ids = $this->db->getColumn(
            'SELECT product_id FROM ?:products WHERE parent_product_id = ?i AND product_type = ?s',
            $product_id, self::PRODUCT_TYPE_VARIATION
        );

        return $product_ids;
    }

    /*
     * Prepares variation selected options for saving
     *
     * @param array $selected_options Variation selected options
     *
     * @return string
     */
    public function encodeVariationSelectedOptions(array $selected_options)
    {
        return json_encode($selected_options);
    }

    /**
     * Converts product to configurable
     *
     * @param int   $product_id Product identifier
     * @param array $option_ids Array with selected option ids
     */
    public function changeProductTypeToConfigurable($product_id, array $option_ids)
    {
        $tracking = $this->getProductFieldValue($product_id, 'tracking');
        $tracking = $this->normalizeTracking($tracking);
        $option_ids = array_map('intval', $option_ids);

        $this->db->query(
            'UPDATE ?:products SET product_type = ?s, variation_options = ?s, tracking = ?s WHERE product_id = ?i',
            self::PRODUCT_TYPE_CONFIGURABLE, json_encode($option_ids), $tracking, $product_id
        );
    }

    /**
     * Normalizes tracking mode value for configurable product.
     *
     * @param string $tracking
     *
     * @return string
     */
    public function normalizeTracking($tracking)
    {
        if ($tracking !== ProductTracking::DO_NOT_TRACK) {
            $tracking = ProductTracking::TRACK_WITHOUT_OPTIONS;
        }

        return $tracking;
    }

    /**
     * Decodes variation option array
     *
     * @param string $variation_options Option ID -> Variation ID pair json string
     *
     * @return array
     */
    public function decodeVariationOptions($variation_options)
    {
        return (array) json_decode($variation_options, true);
    }

    /**
     * Updates prices of product variations.
     *
     * @param int     $product_id Product identifier
     * @param float   $price      Product price
     * @param float[] $prices     Quantity discounts
     * @param array   $auth       Current user authentication data
     */
    public function updateProductVariationsPrices($product_id, $price, $prices, $auth)
    {
        $variation_product_ids = $this->getProductVariations($product_id);

        if (!$variation_product_ids) {
            return;
        }

        $prices = array_filter($prices, function ($price) {
            return !empty($price['lower_limit']);
        });

        foreach ($variation_product_ids as $variation_id) {
            $variation_product_data = array();

            fn_get_product_prices($variation_id, $variation_product_data, $auth);
            if (empty($variation_product_data['prices'])) {
                $variation_product_data['price'] = $price;
                $variation_product_data['prices'] = $prices;
            } else {
                $product_prices = $variation_prices = array();
                foreach ($prices as $price) {
                    $product_prices['limit' . $price['lower_limit']] = $price;
                }

                foreach ($variation_product_data['prices'] as $price) {
                    $variation_prices['limit' . $price['lower_limit']] = $price;
                }

                $variation_product_data['prices'] = array_merge($product_prices, $variation_prices);
            }

            fn_update_product_prices($variation_id, $variation_product_data);
        }
    }

    /**
     * Gets the options of the default variation
     *
     * @param int $product_id The product identifier
     *
     * @return array The options of the default variation
     */
    public function getDefaultVariationOptions($product_id)
    {
        $variation_options = $this->db->getField(
            'SELECT variation_options FROM ?:products WHERE is_default_variation = ?s AND parent_product_id = ?i',
            'Y', $product_id
        );

        $variation_options = $this->decodeVariationOptions($variation_options);

        return $variation_options;
    }

    /**
     * Updates the default variation
     *
     * @param int $variation_product_id The product variation identifier
     * @param int $parent_product_id    The parent product identifier
     */
    public function updateDefaultVariation($variation_product_id = 0, $parent_product_id = 0)
    {
        if (!empty($variation_product_id)) {
            $parent_product_id = $this->getProductFieldValue($variation_product_id, 'parent_product_id');
        }

        if ($parent_product_id) {
            $this->db->query(
                'UPDATE ?:products SET is_default_variation = ?s WHERE parent_product_id = ?i',
                'N', $parent_product_id
            );

            if (!empty($variation_product_id)) {
                $this->db->query(
                    'UPDATE ?:products SET is_default_variation = ?s WHERE product_id = ?i',
                    'Y', $variation_product_id
                );
            }
        }
    }
}
