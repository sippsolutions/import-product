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

use Psr\Log\LoggerInterface;
use TechDivision\Import\Utils\RegistryKeys;
use TechDivision\Import\Utils\Generators\GeneratorInterface;
use TechDivision\Import\Subjects\AbstractEavSubject;
use TechDivision\Import\Product\Utils\MemberNames;
use TechDivision\Import\Product\Services\ProductProcessorInterface;
use TechDivision\Import\Services\RegistryProcessorInterface;
use TechDivision\Import\Configuration\SubjectConfigurationInterface;

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
abstract class AbstractProductSubject extends AbstractEavSubject
{

    /**
     * The processor to read/write the necessary product data.
     *
     * @var \TechDivision\Import\Product\Services\ProductProcessorInterface
     */
    protected $productProcessor;

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
     * Mappings for attribute code => CSV column header.
     *
     * @var array
     */
    protected $headerMappings = array(
        'product_online' => 'status',
        'tax_class_name' => 'tax_class_id',
        'bundle_price_type' => 'price_type',
        'bundle_sku_type' => 'sku_type',
        'bundle_price_view' => 'price_view',
        'bundle_weight_type' => 'weight_type',
        'base_image' => 'image',
        'base_image_label' => 'image_label',
        'thumbnail_image' => 'thumbnail',
        'thumbnail_image_label'=> 'thumbnail_label',
        'bundle_shipment_type' => 'shipment_type',
        'related_skus' => 'relation_skus',
        'related_position' => 'relation_position',
        'crosssell_skus' => 'cross_sell_skus',
        'crosssell_position' => 'cross_sell_position',
        'upsell_skus' => 'up_sell_skus',
        'upsell_position' => 'up_sell_position'
    );

    /**
     * Initialize the subject instance.
     *
     * @param \Psr\Log\LoggerInterface                                         $systemLogger               The system logger instance
     * @param \TechDivision\Import\Configuration\SubjectConfigurationInterface $configuration              The subject configuration instance
     * @param \TechDivision\Import\Services\RegistryProcessorInterface         $registryProcessor          The registry processor instance
     * @param \TechDivision\Import\Utils\Generators\GeneratorInterface         $coreConfigDataUidGenerator The UID generator for the core config data
     * @param \TechDivision\Import\Product\Services\ProductProcessorInterface  $productProcessor           The product processor instance
     */
    public function __construct(
        LoggerInterface $systemLogger,
        SubjectConfigurationInterface $configuration,
        RegistryProcessorInterface $registryProcessor,
        GeneratorInterface $coreConfigDataUidGenerator,
        ProductProcessorInterface $productProcessor
    ) {

        // pass the arguments to the parent constructor
        parent::__construct($systemLogger, $configuration, $registryProcessor, $coreConfigDataUidGenerator);

        // initialize the product processor
        $this->productProcessor = $productProcessor;
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
     * Set's the product processor instance.
     *
     * @param \TechDivision\Import\Product\Services\ProductProcessorInterface $productProcessor The product processor instance
     *
     * @return void
     */
    public function setProductProcessor(ProductProcessorInterface $productProcessor)
    {
        $this->productProcessor = $productProcessor;
    }

    /**
     * Return's the product processor instance.
     *
     * @return \TechDivision\Import\Services\ProductProcessorInterface The product processor instance
     */
    public function getProductProcessor()
    {
        return $this->productProcessor;
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
     * Add the passed SKU => entity ID mapping.
     *
     * @param string $sku The SKU
     *
     * @return void
     */
    public function addSkuEntityIdMapping($sku)
    {
        $this->skuEntityIdMapping[$sku] = $this->getLastEntityId();
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
        $this->skuStoreViewCodeMapping[$sku] = $storeViewCode;
    }

    /**
     * Intializes the previously loaded global data for exactly one bunch.
     *
     * @return void
     * @see \Importer\Csv\Actions\ProductImportAction::prepare()
     */
    public function setUp()
    {

        // load the status of the actual import
        $status = $this->getRegistryProcessor()->getAttribute($this->getSerial());

        // load the global data we've prepared initially
        $this->linkTypes = $status[RegistryKeys::GLOBAL_DATA][RegistryKeys::LINK_TYPES];
        $this->categories = $status[RegistryKeys::GLOBAL_DATA][RegistryKeys::CATEGORIES];
        $this->taxClasses = $status[RegistryKeys::GLOBAL_DATA][RegistryKeys::TAX_CLASSES];
        $this->storeWebsites =  $status[RegistryKeys::GLOBAL_DATA][RegistryKeys::STORE_WEBSITES];

        // invoke the parent method
        parent::setUp();
    }

    /**
     * Clean up the global data after importing the bunch.
     *
     * @return void
     */
    public function tearDown()
    {

        // invoke the parent method
        parent::tearDown();

        // load the registry processor
        $registryProcessor = $this->getRegistryProcessor();

        // update the status with the SKU => entity ID mapping
        $registryProcessor->mergeAttributesRecursive(
            $this->getSerial(),
            array()
        );

        // update the status
        $registryProcessor->mergeAttributesRecursive(
            $this->getSerial(),
            array(
                RegistryKeys::FILES => array($this->getFilename() => array(RegistryKeys::STATUS => 1)),
                RegistryKeys::SKU_ENTITY_ID_MAPPING => $this->skuEntityIdMapping,
                RegistryKeys::SKU_STORE_VIEW_CODE_MAPPING => $this->skuStoreViewCodeMapping
            )
        );
    }

    /**
     * Return's the header mappings for the actual entity.
     *
     * @return array The header mappings
     */
    public function getHeaderMappings()
    {
        return $this->headerMappings;
    }

    /**
     * Return's an array with the available EAV attributes for the passed is user defined flag.
     *
     * @param integer $isUserDefined The flag itself
     *
     * @return array The array with the EAV attributes matching the passed flag
     */
    public function getEavAttributeByIsUserDefined($isUserDefined = 1)
    {
        return $this->getProductProcessor()->getEavAttributeByIsUserDefined($isUserDefined);
    }

    /**
     * Return's the attribute option value with the passed value and store ID.
     *
     * @param mixed   $value   The option value
     * @param integer $storeId The ID of the store
     *
     * @return array|boolean The attribute option value instance
     */
    public function getEavAttributeOptionValueByOptionValueAndStoreId($value, $storeId)
    {
        return $this->getProductProcessor()->getEavAttributeOptionValueByOptionValueAndStoreId($value, $storeId);
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
            sprintf(
                'Found invalid store view code %s in file %s on line %d',
                $storeViewCode,
                $this->getFilename(),
                $this->getLineNumber()
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
            sprintf(
                'Found invalid tax class name %s in file %s on line %d',
                $taxClassName,
                $this->getFilename(),
                $this->getLineNumber()
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
            sprintf(
                'Found invalid website code %s in file %s on line %d',
                $code,
                $this->getFilename(),
                $this->getLineNumber()
            )
        );
    }

    /**
     * Return's the category with the passed path.
     *
     * @param string $path The path of the category to return
     *
     * @return array The category
     * @throws \Exception Is thrown, if the requested category is not available
     */
    public function getCategoryByPath($path)
    {

        // query whether or not the category with the passed path exists
        if (isset($this->categories[$path])) {
            return $this->categories[$path];
        }

        // throw an exception, if not
        throw new \Exception(
            sprintf(
                'Can\'t find category with path %s in file %s on line %d',
                $path,
                $this->getFilename(),
                $this->getLineNumber()
            )
        );
    }

    /**
     * Return's the category with the passed ID.
     *
     * @param integer $categoryId The ID of the category to return
     *
     * @return array The category data
     * @throws \Exception Is thrown, if the category is not available
     */
    public function getCategory($categoryId)
    {

        // try to load the category with the passed ID
        foreach ($this->categories as $category) {
            if ($category[MemberNames::ENTITY_ID] == $categoryId) {
                return $category;
            }
        }

        // throw an exception if the category is NOT available
        throw new \Exception(
            sprintf(
                'Can\'t load category with ID %d in file %s on line %d',
                $categoryId,
                $this->getFilename(),
                $this->getLineNumber()
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
        throw new \Exception(sprintf('Root category for %s is not available', $storeViewCode));
    }
}
