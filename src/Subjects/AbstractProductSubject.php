<?php

/**
 * TechDivision\Import\Product\Subjects\AbstractProductSubject
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

namespace TechDivision\Import\Product\Subjects;

use TechDivision\Import\Utils\RegistryKeys;
use TechDivision\Import\Utils\StoreViewCodes;
use TechDivision\Import\Utils\FrontendInputTypes;
use TechDivision\Import\Product\Utils\MemberNames;
use TechDivision\Import\Product\Utils\RelationTypes;
use TechDivision\Import\Product\Utils\ConfigurationKeys;
use TechDivision\Import\Subjects\AbstractEavSubject;
use TechDivision\Import\Subjects\EntitySubjectInterface;
use TechDivision\Import\Product\Exceptions\MapLinkTypeCodeToIdException;

/**
 * The abstract product subject implementation that provides basic product
 * handling business logic.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product
 * @link      http://www.techdivision.com
 */
abstract class AbstractProductSubject extends AbstractEavSubject implements EntitySubjectInterface, SkuToPkMappingAwareSubjectInterface
{

    /**
     * The trait with the SKU => PK mapping functionality.
     *
     * @var \TechDivision\Import\Product\Subjects\SkuToPkMappingTrait
     */
    use SkuToPkMappingTrait;

    /**
     * The available stores.
     *
     * @var array
     */
    protected $stores = array();

    /**
     * The available store websites.
     *
     * @var array
     */
    protected $storeWebsites = array();

    /**
     * The available EAV attributes, grouped by their attribute set and the attribute set name as keys.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * The available tax classes.
     *
     * @var array
     */
    protected $taxClasses = array();

    /**
     * The available categories.
     *
     * @var array
     */
    protected $categories = array();

    /**
     * The available link types.
     *
     * @var array
     */
    protected $linkTypes = array();

    /**
     * The available link attributes.
     *
     * @var array
     */
    protected $linkAttributes = array();

    /**
     * The ID of the product that has been created recently.
     *
     * @var string
     */
    protected $lastEntityId;

    /**
     * The SKU of the product that has been created recently.
     *
     * @var string
     */
    protected $lastSku;

    /**
     * The Magento 2 configuration.
     *
     * @var array
     */
    protected $coreConfigData;

    /**
     * The mapping for the SKUs to the created entity IDs.
     *
     * @var array
     */
    protected $skuEntityIdMapping = array();

    /**
     * The mapping for the SKUs to the store view codes.
     *
     * @var array
     */
    protected $skuStoreViewCodeMapping = array();

    /**
     * The array with the available image types and their label columns.
     *
     * @var array
     */
    protected $imageTypes = array();

    /**
     * Mappings for CSV column header => attribute code.
     *
     * @var array
     */
    protected $headerMappings = array(
        'product_online'       => 'status',
        'tax_class_name'       => 'tax_class_id',
        'bundle_price_type'    => 'price_type',
        'bundle_sku_type'      => 'sku_type',
        'bundle_price_view'    => 'price_view',
        'bundle_weight_type'   => 'weight_type',
        'bundle_shipment_type' => 'shipment_type',
        'related_skus'         => 'relation_skus',
        'related_position'     => 'relation_position',
        'crosssell_skus'       => 'cross_sell_skus',
        'crosssell_position'   => 'cross_sell_position',
        'upsell_skus'          => 'up_sell_skus',
        'upsell_position'      => 'up_sell_position',
        'msrp_price'           => 'msrp',
        'base_image'           => 'image',
        'base_image_label'     => 'image_label',
        'thumbnail_image'      => 'thumbnail',
        'thumbnail_image_label'=> 'thumbnail_label'
    );

    /**
     * The default mappings for the user defined attributes, based on the attributes frontend input type.
     *
     * @var array
     */
    protected $defaultFrontendInputCallbackMappings = array(
        FrontendInputTypes::SELECT      => array('import_product.callback.select'),
        FrontendInputTypes::MULTISELECT => array('import_product.callback.multiselect'),
        FrontendInputTypes::BOOLEAN     => array('import_product.callback.boolean')
    );

    /**
     * Array that contains the relations that has already been processed.
     *
     * @var array
     */
    protected $processedRelations = array();

    /**
     * The array that contains the prepared link type mappings.
     *
     * @var array
     */
    protected $linkTypeMappings = array();

    /**
     * The array that contains the link type => column name prefix mapping.
     *
     * @var array
     */
    protected $linkTypeCodeToColumnNameMapping = array('super' => 'associated');

    /**
     * The array that contains the link type attribute column => callback mapping.
     *
     * @var array
     */
    protected $linkTypeAttributeColumnToCallbackMapping = array(
        'associated_skus' => array('associated_skus', 'explodeKey'),
        'associated_qty'  => array('associated_skus', 'explodeValue')
    );

    /**
     * Return's the default callback frontend input mappings for the user defined attributes.
     *
     * @return array The default frontend input callback mappings
     */
    public function getDefaultFrontendInputCallbackMappings()
    {
        return $this->defaultFrontendInputCallbackMappings;
    }

    /**
     * Return's the available link types.
     *
     * @return array The link types
     */
    public function getLinkTypes()
    {
        return $this->linkTypes;
    }

    /**
     * Set's the SKU of the last imported product.
     *
     * @param string $lastSku The SKU
     *
     * @return void
     */
    public function setLastSku($lastSku)
    {
        $this->lastSku = $lastSku;
    }

    /**
     * Return's the SKU of the last imported product.
     *
     * @return string|null The SKU
     */
    public function getLastSku()
    {
        return $this->lastSku;
    }

    /**
     * Set's the ID of the product that has been created recently.
     *
     * @param string $lastEntityId The entity ID
     *
     * @return void
     */
    public function setLastEntityId($lastEntityId)
    {
        $this->lastEntityId = $lastEntityId;
    }

    /**
     * Return's the ID of the product that has been created recently.
     *
     * @return string The entity Id
     */
    public function getLastEntityId()
    {
        return $this->lastEntityId;
    }

    /**
     * Queries whether or not the SKU has already been processed.
     *
     * @param string $sku The SKU to check been processed
     *
     * @return boolean TRUE if the SKU has been processed, else FALSE
     */
    public function hasBeenProcessed($sku)
    {
        return isset($this->skuEntityIdMapping[$sku]);
    }

    /**
     * Queries whether or not the passed PK and store view code has already been processed.
     *
     * @param string $pk            The PK to check been processed
     * @param string $storeViewCode The store view code to check been processed
     *
     * @return boolean TRUE if the PK and store view code has been processed, else FALSE
     */
    public function storeViewHasBeenProcessed($pk, $storeViewCode)
    {
        return isset($this->skuEntityIdMapping[$pk]) && isset($this->skuStoreViewCodeMapping[$pk]) && in_array($storeViewCode, $this->skuStoreViewCodeMapping[$pk]);
    }

    /**
     * Add the passed SKU => entity ID mapping.
     *
     * @param string       $sku      The SKU
     * @param integer|null $entityId The optional entity ID, the last processed entity ID is used, if not set
     *
     * @return void
     */
    public function addSkuEntityIdMapping($sku, $entityId = null)
    {
        $this->skuEntityIdMapping[$sku] = $entityId == null ? $this->getLastEntityId() : $entityId;
    }

    /**
     * Add the passed SKU => store view code mapping.
     *
     * @param string $sku           The SKU
     * @param string $storeViewCode The store view code
     *
     * @return void
     */
    public function addSkuStoreViewCodeMapping($sku, $storeViewCode)
    {
        $this->skuStoreViewCodeMapping[$sku][] = $storeViewCode;
    }

    /**
     * Intializes the previously loaded global data for exactly one bunch.
     *
     * @param string $serial The serial of the actual import
     *
     * @return void
     */
    public function setUp($serial)
    {

        // load the status of the actual import
        $status = $this->getRegistryProcessor()->getAttribute(RegistryKeys::STATUS);

        // load the global data we've prepared initially
        $this->linkTypes = $status[RegistryKeys::GLOBAL_DATA][RegistryKeys::LINK_TYPES];
        $this->categories = $status[RegistryKeys::GLOBAL_DATA][RegistryKeys::CATEGORIES];
        $this->taxClasses = $status[RegistryKeys::GLOBAL_DATA][RegistryKeys::TAX_CLASSES];
        $this->imageTypes =  $status[RegistryKeys::GLOBAL_DATA][RegistryKeys::IMAGE_TYPES];
        $this->storeWebsites =  $status[RegistryKeys::GLOBAL_DATA][RegistryKeys::STORE_WEBSITES];
        $this->linkAttributes = $status[RegistryKeys::GLOBAL_DATA][RegistryKeys::LINK_ATTRIBUTES];

        // prepare the link type mappings
        $this->linkTypeMappings = $this->prepareLinkTypeMappings();

        // invoke the parent method
        parent::setUp($serial);
    }


    /**
     * Clean up the global data after importing the bunch.
     *
     * @param string $serial The serial of the actual import
     *
     * @return void
     */
    public function tearDown($serial)
    {

        // load the registry processor
        $registryProcessor = $this->getRegistryProcessor();

        // update the status
        $registryProcessor->mergeAttributesRecursive(
            RegistryKeys::STATUS,
            array(
                RegistryKeys::SKU_ENTITY_ID_MAPPING => $this->skuEntityIdMapping,
                RegistryKeys::SKU_STORE_VIEW_CODE_MAPPING => $this->skuStoreViewCodeMapping
            )
        );

        // invoke the parent method
        parent::tearDown($serial);
    }

    /**
     * Return's the available image types.
     *
     * @return array The array with the available image types
     */
    public function getImageTypes()
    {
        return $this->imageTypes;
    }

    /**
     * Return's the link type code => colums mapping.
     *
     * @return array The mapping with the link type codes => colums
     */
    public function getLinkTypeMappings()
    {
        return $this->linkTypeMappings;
    }

    /**
     * Return's the store ID of the actual row, or of the default store
     * if no store view code is set in the CSV file.
     *
     * @param string|null $default The default store view code to use, if no store view code is set in the CSV file
     *
     * @return integer The ID of the actual store
     * @throws \Exception Is thrown, if the store with the actual code is not available
     */
    public function getRowStoreId($default = null)
    {

        // initialize the default store view code, if not passed
        if ($default == null) {
            $defaultStore = $this->getDefaultStore();
            $default = $defaultStore[MemberNames::CODE];
        }

        // load the store view code the create the product/attributes for
        $storeViewCode = $this->getStoreViewCode($default);

        // query whether or not, the requested store is available
        if (isset($this->stores[$storeViewCode])) {
            return (integer) $this->stores[$storeViewCode][MemberNames::STORE_ID];
        }

        // throw an exception, if not
        throw new \Exception(
            $this->appendExceptionSuffix(
                sprintf('Found invalid store view code %s', $storeViewCode)
            )
        );
    }

    /**
     * Return's the tax class ID for the passed tax class name.
     *
     * @param string $taxClassName The tax class name to return the ID for
     *
     * @return integer The tax class ID
     * @throws \Exception Is thrown, if the tax class with the requested name is not available
     */
    public function getTaxClassIdByTaxClassName($taxClassName)
    {

        // query whether or not, the requested tax class is available
        if (isset($this->taxClasses[$taxClassName])) {
            return (integer) $this->taxClasses[$taxClassName][MemberNames::CLASS_ID];
        }

        // throw an exception, if not
        throw new \Exception(
            $this->appendExceptionSuffix(
                sprintf('Found invalid tax class name %s', $taxClassName)
            )
        );
    }

    /**
     * Return's the store website for the passed code.
     *
     * @param string $code The code of the store website to return the ID for
     *
     * @return integer The store website ID
     * @throws \Exception Is thrown, if the store website with the requested code is not available
     */
    public function getStoreWebsiteIdByCode($code)
    {

        // query whether or not, the requested store website is available
        if (isset($this->storeWebsites[$code])) {
            return (integer) $this->storeWebsites[$code][MemberNames::WEBSITE_ID];
        }

        // throw an exception, if not
        throw new \Exception(
            $this->appendExceptionSuffix(
                sprintf('Found invalid website code %s', $code)
            )
        );
    }

    /**
     * Return's the category with the passed path.
     *
     * @param string $path          The path of the category to return
     * @param string $storeViewCode The code of a store view, defaults to admin
     *
     * @return array The category
     * @throws \Exception Is thrown, if the requested category is not available
     */
    public function getCategoryByPath($path, $storeViewCode = StoreViewCodes::ADMIN)
    {

        // load the categories by the passed store view code
        $categories = $this->getCategoriesByStoreViewCode($storeViewCode);

        // query whether or not the category with the passed path exists
        if (isset($categories[$path])) {
            return $categories[$path];
        }

        // throw an exception, if not
        throw new \Exception(
            $this->appendExceptionSuffix(
                sprintf('Can\'t find category with path %s', $path)
            )
        );
    }

    /**
     * Retrieve categories by given store view code.
     *
     * @param string $storeViewCode The store view code to retrieve the categories for
     *
     * @return array The array with the categories for the passed store view code
     */
    public function getCategoriesByStoreViewCode($storeViewCode)
    {
        return isset($this->categories[$storeViewCode]) ? $this->categories[$storeViewCode] : array();
    }

    /**
     * Return's the category with the passed ID.
     *
     * @param integer $categoryId    The ID of the category to return
     * @param string  $storeViewCode The code of a store view, defaults to "admin"
     *
     * @return array The category data
     * @throws \Exception Is thrown, if the category is not available
     */
    public function getCategory($categoryId, $storeViewCode = StoreViewCodes::ADMIN)
    {

        // retrieve the categories with for the passed store view code
        $categories = $this->getCategoriesByStoreViewCode($storeViewCode);

        // try to load the category with the passed ID
        foreach ($categories as $category) {
            if ($category[MemberNames::ENTITY_ID] == $categoryId) {
                return $category;
            }
        }

        // throw an exception if the category is NOT available
        throw new \Exception(
            $this->appendExceptionSuffix(
                sprintf('Can\'t load category with ID %d', $categoryId)
            )
        );
    }

    /**
     * Return's the root category for the actual view store.
     *
     * @return array The store's root category
     * @throws \Exception Is thrown if the root category for the passed store code is NOT available
     */
    public function getRootCategory()
    {

        // load the default store
        $defaultStore = $this->getDefaultStore();

        // load the actual store view code
        $storeViewCode = $this->getStoreViewCode($defaultStore[MemberNames::CODE]);

        // query weather or not we've a root category or not
        if (isset($this->rootCategories[$storeViewCode])) {
            return $this->rootCategories[$storeViewCode];
        }

        // throw an exception if the root category is NOT available
        throw new \Exception(
            $this->appendExceptionSuffix(
                sprintf('Root category for %s is not available', $storeViewCode)
            )
        );
    }

    /**
     * Returns an array with the codes of the store views related with the passed website code.
     *
     * @param string $websiteCode The code of the website to return the store view codes for
     *
     * @return array The array with the matching store view codes
     */
    public function getStoreViewCodesByWebsiteCode($websiteCode)
    {

        // query whether or not the website with the passed code exists
        if (!isset($this->storeWebsites[$websiteCode])) {
            // throw an exception if the website is NOT available
            throw new \Exception(
                $this->appendExceptionSuffix(
                    sprintf('Website with code "%s" is not available', $websiteCode)
                )
            );
        }

        // initialize the array for the store view codes
        $storeViewCodes = array();

        // load the website ID
        $websiteId = (integer) $this->storeWebsites[$websiteCode][MemberNames::WEBSITE_ID];

        // iterate over the available stores to find the one of the website
        foreach ($this->stores as $storeCode => $store) {
            if ((integer) $store[MemberNames::WEBSITE_ID] === $websiteId) {
                $storeViewCodes[] = $storeCode;
            }
        }

        // return the array with the matching store view codes
        return $storeViewCodes;
    }

    /**
     * Merge the columns from the configuration with all image type columns to define which
     * columns should be cleaned-up.
     *
     * @return array The columns that has to be cleaned-up
     */
    public function getCleanUpColumns()
    {

        // load the colums that has to be cleaned-up
        $cleanUpColumns = $this->getConfiguration()->getParam(ConfigurationKeys::CLEAN_UP_EMPTY_COLUMNS);

        // query whether or not the image columns has to be cleaned-up also
        if ($this->getConfiguration()->hasParam(ConfigurationKeys::CLEAN_UP_EMPTY_IMAGE_COLUMNS) &&
            $this->getConfiguration()->hasParam(ConfigurationKeys::CLEAN_UP_EMPTY_IMAGE_COLUMNS, false)
        ) {
            // if yes load the image column names
            $imageTypes = array_keys($this->getImageTypes());

            // and append them to the column names from the configuration
            foreach ($imageTypes as $imageAttribute) {
                $cleanUpColumns[] = $imageAttribute;
            }
        }

        // return the array with the column names that has to be cleaned-up
        return $cleanUpColumns;
    }

    /**
     * Marks the relation combination processed.
     *
     * @param string $key   The key of the relation
     * @param string $value One of the relation values
     * @param string $type  The relation type to add
     *
     * @return void
     */
    public function addProcessedRelation($key, $value, $type = RelationTypes::PRODUCT_RELATION)
    {

        // query whether or not the child SKU has already been processed
        if (isset($this->processedRelations[$type][$key])) {
            $this->processedRelations[$type][$key][] = $value;
        } else {
            $this->processedRelations[$type][$key] = array($value);
        }
    }

    /**
     * Query's whether or not the relation with the passed key
     * value combination and the given type has been processed.
     *
     * @param string $key   The key of the relation
     * @param string $value One of the relation values
     * @param string $type  The relation type to add
     *
     * @return boolean TRUE if the combination has been processed, else FALSE
     */
    public function hasBeenProcessedRelation($key, $value, $type = RelationTypes::PRODUCT_RELATION)
    {

        // query whether or not the parent SKU has already been registered
        if (isset($this->processedRelations[$type][$key])) {
            return in_array($value, $this->processedRelations[$type][$key]);
        }

        // return FALSE if NOT
        return false;
    }

    /**
     * Return's the link type ID for the passed link type code.
     *
     * @param string $linkTypeCode The link type code to return the link type ID for
     *
     * @return integer The mapped link type ID
     * @throws \TechDivision\Import\Product\Exceptions\MapLinkTypeCodeToIdException Is thrown if the link type code is not mapped yet
     */
    public function mapLinkTypeCodeToLinkTypeId($linkTypeCode)
    {

        // query weather or not the link type code has been mapped
        if (isset($this->linkTypes[$linkTypeCode])) {
            return $this->linkTypes[$linkTypeCode][MemberNames::LINK_TYPE_ID];
        }

        // throw an exception if the link type code has not been mapped yet
        throw new MapLinkTypeCodeToIdException(
            $this->appendExceptionSuffix(
                sprintf('Found not mapped link type code %s', $linkTypeCode)
            )
        );
    }

    /**
     * Return's the link attribute for the passed link type ID and attribute code.
     *
     * @param integer $linkTypeId    The link type
     * @param string  $attributeCode The attribute code
     *
     * @return array The link attribute
     */
    public function getProductLinkAttribute($linkTypeId, $attributeCode)
    {

        // try to load the link attribute with the passed link type ID and attribute code
        foreach ($this->linkAttributes as $linkAttribute) {
            if ($linkAttribute[MemberNames::LINK_TYPE_ID] === $linkTypeId &&
                $linkAttribute[MemberNames::PRODUCT_LINK_ATTRIBUTE_CODE] === $attributeCode
            ) {
                // return the matching link attribute
                return $linkAttribute;
            }
        }
    }

    /**
     * Return's the link attribute for the passed link type and attribute code.
     *
     * @param string $linkTypeCode  The link type code
     * @param string $attributeCode The attribute code
     *
     * @return array The link attribute
     */
    public function getProductLinkAttributeByLinkTypeCodeAndAttributeCode($linkTypeCode, $attributeCode)
    {

        // map the link type code => ID
        $linkTypeId = $this->mapLinkTypeCodeToLinkTypeId($linkTypeCode);

        // try to load the link attribute with the passed link type ID and attribute code
        foreach ($this->linkAttributes as $linkAttribute) {
            if ($linkAttribute[MemberNames::LINK_TYPE_ID] === $linkTypeId &&
                $linkAttribute[MemberNames::PRODUCT_LINK_ATTRIBUTE_CODE] === $attributeCode
            ) {
                // return the matching link attribute
                return $linkAttribute;
            }
        }
    }

    /**
     * Returns the product link attributes for the passed link type code.
     *
     * @param string $linkTypeCode The link type code
     *
     * @return array The product link types
     */
    public function getProductLinkAttributes($linkTypeCode)
    {

        // map the link type code => ID
        $linkTypeId = $this->mapLinkTypeCodeToLinkTypeId($linkTypeCode);

        // initialize the array for the link attributes
        $linkAttributes = array();

        // try to load the link attribute with the passed link type ID and attribute code
        foreach ($this->linkAttributes as $linkAttribute) {
            if ($linkAttribute[MemberNames::LINK_TYPE_ID] === $linkTypeId) {
                // return the matching link attribute
                $linkAttributes[] = $linkAttribute;
            }
        }

        // return the link attributes
        return $linkAttributes;
    }

    /**
     * Maps the link type code to the apropriate column name.
     *
     * @param string $linkTypeCode The link type code to map
     *
     * @return string The mapped column name
     */
    public function mapLinkTypeCodeToColumnName($linkTypeCode)
    {

        // query whether or not the link type code has a mapping
        if (isset($this->linkTypeCodeToColumnNameMapping[$linkTypeCode])) {
            return $this->linkTypeCodeToColumnNameMapping[$linkTypeCode];
        }

        // return the passed link type code
        return $linkTypeCode;
    }

    /**
     * Return's the link type code => colums mapping.
     *
     * @return array The mapping with the link type codes => colums
     */
    public function prepareLinkTypeMappings()
    {

        // initialize the array with link type mappings
        $linkTypeMappings = array();

        // prepare the link type mappings
        foreach ($this->getLinkTypes() as $linkType) {
            // map the link type code to the column name, if necessary
            $columnName = $this->mapLinkTypeCodeToColumnName($linkType[MemberNames::CODE]);

            // create the header for the link type mapping
            $linkTypeMappings[$linkType[MemberNames::CODE]][] = array (
                $fullColumnName = sprintf('%s_skus', $columnName),
                $this->getLinkTypeColumnCallback($fullColumnName)
            );

            // add the mappings for the columns that contains the values for the configured link type attributes
            foreach ($this->getProductLinkAttributes($linkType[MemberNames::CODE]) as $linkAttribute) {
                // initialize the full column name that uses the column name as prefix and the attribute code as suffix
                $fullColumnName = sprintf('%s_%s', $columnName, $linkAttribute[MemberNames::PRODUCT_LINK_ATTRIBUTE_CODE]);
                // load the callback that extracts the values from the columns
                $callback = $this->getLinkTypeColumnCallback($fullColumnName);

                // map the column name to the real column name
                if (isset($this->linkTypeAttributeColumnToCallbackMapping[$fullColumnName])) {
                    list ($fullColumnName, ) = $this->linkTypeAttributeColumnToCallbackMapping[$fullColumnName];
                }

                // add the link type mapping for the column with the link type value
                $linkTypeMappings[$linkType[MemberNames::CODE]][] = array(
                    $fullColumnName,
                    $callback,
                    $linkAttribute[MemberNames::PRODUCT_LINK_ATTRIBUTE_CODE]
                );
            }
        }

        // return the link type mappings
        return $linkTypeMappings;
    }

    /**
     * Returns the callback method used to extract the value of the passed
     * column to create the link type attribute value with.
     *
     * @param string $columnName The column name to create the callback for
     *
     * @return callable The callback
     */
    public function getLinkTypeColumnCallback($columnName)
    {

        // query whether or not a callback mapping is available
        if (isset($this->linkTypeAttributeColumnToCallbackMapping[$columnName])) {
            // load it from the array with the mappings
            list (, $callbackName) = $this->linkTypeAttributeColumnToCallbackMapping[$columnName];
            // prepare and return the callback
            return array($this, $callbackName);
        }

        // return the default callback
        return array($this, 'explode');
    }

    /**
     * Extracts the keys of the passed value by exploding them
     * with the also passed delimiter/value delmiter.
     *
     * @param string $value          The value to extract
     * @param string $delimiter      The delimiter used to extract the elements
     * @param string $valueDelimiter The delimiter used to extract the key from the value
     *
     * @return array The exploded keys
     */
    public function explodeKey($value, $delimiter = ',', $valueDelimiter = '=')
    {

        // initialize the array for the keys
        $keys = array();

        // extract the keys from the value
        foreach ($this->explode($value, $delimiter) as $keyValue) {
            list($keys[], ) = $this->explode($keyValue, $valueDelimiter);
        }

        // return the array with the keys
        return $keys;
    }

    /**
     * Extracts the values of the passed value by exploding them
     * with the also passed delimiter/value delimiter.
     *
     * @param string $value          The value to extract
     * @param string $delimiter      The delimiter used to extract the elements
     * @param string $valueDelimiter The delimiter used to extract the key from the value
     *
     * @return array The exploded values
     */
    public function explodeValue($value, $delimiter = ',', $valueDelimiter = '=')
    {

        // initialize the array for the values
        $values = array();

        // extract the values from the value
        foreach ($this->explode($value, $delimiter) as $keyValue) {
            list(, $values[]) = $this->explode($keyValue, $valueDelimiter);
        }

        // return the array with the values
        return $values;
    }
}
