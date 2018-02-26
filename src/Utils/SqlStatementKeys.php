<?php

/**
 * TechDivision\Import\Product\Utils\SqlStatements
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Product\Utils;

/**
 * Utility class with the SQL statements to use.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product
 * @link      http://www.techdivision.com
 */
class SqlStatementKeys extends \TechDivision\Import\Utils\SqlStatementKeys
{

    /**
     * The SQL statement to load the product with the passed SKU.
     *
     * @var string
     */
    const PRODUCT = 'product';

    /**
     * The SQL statement to load the product website relations with the passed product/website ID.
     *
     * @var string
     */
    const PRODUCT_WEBSITE = 'product_website';

    /**
     * The SQL statement to load the product datetime attribute with the passed entity/attribute/store ID.
     *
     * @var string
     */
    const PRODUCT_DATETIME = 'product_datetime';

    /**
     * The SQL statement to load the product decimal attribute with the passed entity/attribute/store ID.
     *
     * @var string
     */
    const PRODUCT_DECIMAL = 'product_decimal';

    /**
     * The SQL statement to load the product integer attribute with the passed entity/attribute/store ID.
     *
     * @var string
     */
    const PRODUCT_INT = 'product_int';

    /**
     * The SQL statement to load the product text attribute with the passed entity/attribute/store ID.
     *
     * @var string
     */
    const PRODUCT_TEXT = 'product_text';

    /**
     * The SQL statement to load the product varchar attribute with the passed entity/attribute/store ID.
     *
     * @var string
     */
    const PRODUCT_VARCHAR = 'product_varchar';

    /**
     * The SQL statement to load a product varchar attribute by the passed attribute code,
     * entity typy and store ID as well as the passed value.
     *
     * @var string
     */
    const PRODUCT_VARCHAR_BY_ATTRIBUTE_CODE_AND_ENTITY_TYPE_ID_AND_STORE_ID_AND_VALUE = 'product_varchar.by.attribute_code.and.entity_type_id.and.store_id.and.value';

    /**
     * The SQL statement to load the category product relations with the passed product/website ID.
     *
     * @var string
     */
    const CATEGORY_PRODUCT = 'category_product';

    /**
     * The SQL statement to load the category product relations with the passed product SKU.
     *
     * @var string
     */
    const CATEGORY_PRODUCT_BY_SKU = 'category_product.by.sku';

    /**
     * The SQL statement to load the stock status with the passed product/website/stock ID.
     *
     * @var string
     */
    const STOCK_STATUS = 'stock_status';

    /**
     * The SQL statement to load the stock item with the passed product/website/stock ID.
     *
     * @var string
     */
    const STOCK_ITEM = 'stock_item';

    /**
     * The SQL statement to create new products.
     *
     * @var string
     */
    const CREATE_PRODUCT = 'create.product';

    /**
     * The SQL statement to update an existing product.
     *
     * @var string
     */
    const UPDATE_PRODUCT = 'update.product';

    /**
     * The SQL statement to create a new product website relation.
     *
     * @var string
     */
    const CREATE_PRODUCT_WEBSITE = 'create.product_website';

    /**
     * The SQL statement to create a new category product relation.
     *
     * @var string
     */
    const CREATE_CATEGORY_PRODUCT = 'create.category_product';

    /**
     * The SQL statement to update an existing category product relation.
     *
     * @var string
     */
    const UPDATE_CATEGORY_PRODUCT = 'update.category_product';

    /**
     * The SQL statement to create a new product datetime value.
     *
     * @var string
     */
    const CREATE_PRODUCT_DATETIME = 'create.product_datetime';

    /**
     * The SQL statement to update an existing product datetime value.
     *
     * @var string
     */
    const UPDATE_PRODUCT_DATETIME = 'update.product_datetime';

    /**
     * The SQL statement to delete an existing product datetime value.
     *
     * @var string
     */
    const DELETE_PRODUCT_DATETIME = 'delete.product_datetime';

    /**
     * The SQL statement to create a new product decimal value.
     *
     * @var string
     */
    const CREATE_PRODUCT_DECIMAL = 'create.product_decimal';

    /**
     * The SQL statement to update an existing product decimal value.
     *
     * @var string
     */
    const UPDATE_PRODUCT_DECIMAL = 'update.product_decimal';

    /**
     * The SQL statement to delete an existing product decimal value.
     *
     * @var string
     */
    const DELETE_PRODUCT_DECIMAL = 'delete.product_decimal';

    /**
     * The SQL statement to create a new product integer value.
     *
     * @var string
     */
    const CREATE_PRODUCT_INT = 'create.product_int';

    /**
     * The SQL statement to update an existing product integer value.
     *
     * @var string
     */
    const UPDATE_PRODUCT_INT = 'update.product_int';

    /**
     * The SQL statement to delete an existing product integer value.
     *
     * @var string
     */
    const DELETE_PRODUCT_INT = 'delete.product_int';

    /**
     * The SQL statement to create a new product varchar value.
     *
     * @var string
     */
    const CREATE_PRODUCT_VARCHAR = 'create.product_varchar';

    /**
     * The SQL statement to update an existing product varchar value.
     *
     * @var string
     */
    const UPDATE_PRODUCT_VARCHAR = 'update.product_varchar';

    /**
     * The SQL statement to delete an existing product varchar value.
     *
     * @var string
     */
    const DELETE_PRODUCT_VARCHAR = 'delete.product_varchar';

    /**
     * The SQL statement to create a new product text value.
     *
     * @var string
     */
    const CREATE_PRODUCT_TEXT = 'create.product_text';

    /**
     * The SQL statement to update an existing product text value.
     *
     * @var string
     */
    const UPDATE_PRODUCT_TEXT = 'update.product_text';

    /**
     * The SQL statement to delete an existing product text value.
     *
     * @var string
     */
    const DELETE_PRODUCT_TEXT = 'delete.product_text';

    /**
     * The SQL statement to create a product's stock status.
     *
     * @var string
     */
    const CREATE_STOCK_STATUS = 'create.stock_status';

    /**
     * The SQL statement to update an existing stock status.
     *
     * @var string
     */
    const UPDATE_STOCK_STATUS = 'update.stock_status';

    /**
     * The SQL statement to create a product's stock status.
     *
     * @var string
     */
    const CREATE_STOCK_ITEM = 'create.stock_item';

    /**
     * The SQL statement to create a product's stock status.
     *
     * @var string
     */
    const UPDATE_STOCK_ITEM = 'update.stock_item';

    /**
     * The SQL statement to remove a existing product.
     *
     * @var string
     */
    const DELETE_PRODUCT = 'delete.product';

    /**
     * The SQL statement to remove all existing stock status related with the SKU passed as parameter.
     *
     * @var string
     */
    const DELETE_STOCK_STATUS_BY_SKU = 'delete.stock_status.by.sku';

    /**
     * The SQL statement to remove all existing stock item related with the SKU passed as parameter.
     *
     * @var string
     */
    const DELETE_STOCK_ITEM_BY_SKU = 'delete.stock_item.by.sku';

    /**
     * The SQL statement to remove all product website relations for the product with the SKU passed as parameter.
     *
     * @var string
     */
    const DELETE_PRODUCT_WEBSITE_BY_SKU = 'delete.product_website.by.sku';

    /**
     * The SQL statement to remove all product category relations for the product.
     *
     * @var string
     */
    const DELETE_CATEGORY_PRODUCT = 'delete.category_product';

    /**
     * The SQL statement to remove all product category relations for the product with the SKU passed as parameter.
     *
     * @var string
     */
    const DELETE_CATEGORY_PRODUCT_BY_SKU = 'delete.category_product.by.sku';
}