<?php

/**
 * TechDivision\Import\Product\Subjects\BunchSubject
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

use TechDivision\Import\Utils\StoreViewCodes;
use TechDivision\Import\Product\Utils\MemberNames;
use TechDivision\Import\Product\Utils\RegistryKeys;
use TechDivision\Import\Product\Utils\VisibilityKeys;
use TechDivision\Import\Product\Utils\ConfigurationKeys;
use TechDivision\Import\Subjects\ExportableTrait;
use TechDivision\Import\Subjects\FileUploadTrait;
use TechDivision\Import\Subjects\ExportableSubjectInterface;
use TechDivision\Import\Subjects\FileUploadSubjectInterface;
use TechDivision\Import\Subjects\UrlKeyAwareSubjectInterface;

/**
 * The subject implementation that handles the business logic to persist products.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product
 * @link      http://www.techdivision.com
 */
class BunchSubject extends AbstractProductSubject implements ExportableSubjectInterface, FileUploadSubjectInterface, UrlKeyAwareSubjectInterface
{

    /**
     * The trait that implements the export functionality.
     *
     * @var \TechDivision\Import\Subjects\ExportableTrait
     */
    use ExportableTrait;

    /**
     * The trait that provides file upload functionality.
     *
     * @var \TechDivision\Import\Subjects\FileUploadTrait
     */
    use FileUploadTrait;

    /**
     * The array with the pre-loaded entity IDs.
     *
     * @var array
     */
    protected $preLoadedEntityIds = array();

    /**
     * Mappings for the table column => CSV column header.
     *
     * @var array
     */
    protected $headerStockMappings = array(
        'qty'                         => array('qty', 'float'),
        'min_qty'                     => array('out_of_stock_qty', 'float'),
        'use_config_min_qty'          => array('use_config_min_qty', 'int'),
        'is_qty_decimal'              => array('is_qty_decimal', 'int'),
        'backorders'                  => array('allow_backorders', 'int'),
        'use_config_backorders'       => array('use_config_backorders', 'int'),
        'min_sale_qty'                => array('min_cart_qty', 'float'),
        'use_config_min_sale_qty'     => array('use_config_min_sale_qty', 'int'),
        'max_sale_qty'                => array('max_cart_qty', 'float'),
        'use_config_max_sale_qty'     => array('use_config_max_sale_qty', 'int'),
        'is_in_stock'                 => array('is_in_stock', 'int'),
        'notify_stock_qty'            => array('notify_on_stock_below', 'float'),
        'use_config_notify_stock_qty' => array('use_config_notify_stock_qty', 'int'),
        'manage_stock'                => array('manage_stock', 'int'),
        'use_config_manage_stock'     => array('use_config_manage_stock', 'int'),
        'use_config_qty_increments'   => array('use_config_qty_increments', 'int'),
        'qty_increments'              => array('qty_increments', 'float'),
        'use_config_enable_qty_inc'   => array('use_config_enable_qty_inc', 'int'),
        'enable_qty_increments'       => array('enable_qty_increments', 'int'),
        'is_decimal_divided'          => array('is_decimal_divided', 'int'),
    );

    /**
     * The array with the available visibility keys.
     *
     * @var array
     */
    protected $availableVisibilities = array(
        'Not Visible Individually' => VisibilityKeys::VISIBILITY_NOT_VISIBLE,
        'Catalog'                  => VisibilityKeys::VISIBILITY_IN_CATALOG,
        'Search'                   => VisibilityKeys::VISIBILITY_IN_SEARCH,
        'Catalog, Search'          => VisibilityKeys::VISIBILITY_BOTH
    );

    /**
     * The default callback mappings for the Magento standard product attributes.
     *
     * @var array
     */
    protected $defaultCallbackMappings = array(
        'visibility'           => array('import_product.callback.visibility'),
        'tax_class_id'         => array('import_product.callback.tax.class'),
        'bundle_price_type'    => array('import_product_bundle.callback.bundle.type'),
        'bundle_sku_type'      => array('import_product_bundle.callback.bundle.type'),
        'bundle_weight_type'   => array('import_product_bundle.callback.bundle.type'),
        'bundle_price_view'    => array('import_product_bundle.callback.bundle.price.view'),
        'bundle_shipment_type' => array('import_product_bundle.callback.bundle.shipment.type')
    );

    /**
     * The available entity types.
     *
     * @var array
     */
    protected $entityTypes = array();

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
        $this->entityTypes = $status[RegistryKeys::GLOBAL_DATA][RegistryKeys::ENTITY_TYPES];

        // initialize the flag whether to copy images or not
        if ($this->getConfiguration()->hasParam(ConfigurationKeys::COPY_IMAGES)) {
            $this->setCopyImages($this->getConfiguration()->getParam(ConfigurationKeys::COPY_IMAGES));
        }

        // initialize media directory => can be absolute or relative
        if ($this->getConfiguration()->hasParam(ConfigurationKeys::MEDIA_DIRECTORY)) {
            $this->setMediaDir(
                $this->resolvePath(
                    $this->getConfiguration()->getParam(ConfigurationKeys::MEDIA_DIRECTORY)
                )
            );
        }

        // initialize images directory => can be absolute or relative
        if ($this->getConfiguration()->hasParam(ConfigurationKeys::IMAGES_FILE_DIRECTORY)) {
            $this->setImagesFileDir(
                $this->resolvePath(
                    $this->getConfiguration()->getParam(ConfigurationKeys::IMAGES_FILE_DIRECTORY)
                )
            );
        }

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

        // invoke the parent method
        parent::tearDown($serial);

        // load the registry processor
        $registryProcessor = $this->getRegistryProcessor();

        // update the status
        $registryProcessor->mergeAttributesRecursive(
            RegistryKeys::STATUS,
            array(
                RegistryKeys::PRE_LOADED_ENTITY_IDS => $this->preLoadedEntityIds,
            )
        );
    }

    /**
     * Return's the default callback mappings.
     *
     * @return array The default callback mappings
     */
    public function getDefaultCallbackMappings()
    {
        return $this->defaultCallbackMappings;
    }

    /**
     * Return's the mappings for the table column => CSV column header.
     *
     * @return array The header stock mappings
     */
    public function getHeaderStockMappings()
    {
        return $this->headerStockMappings;
    }

    /**
     * Return's the visibility key for the passed visibility string.
     *
     * @param string $visibility The visibility string to return the key for
     *
     * @return integer The requested visibility key
     * @throws \Exception Is thrown, if the requested visibility is not available
     */
    public function getVisibilityIdByValue($visibility)
    {

        // query whether or not, the requested visibility is available
        if (isset($this->availableVisibilities[$visibility])) {
            // load the visibility ID, add the mapping and return the ID
            return $this->availableVisibilities[$visibility];
        }

        // throw an exception, if not
        throw new \Exception(
            $this->appendExceptionSuffix(
                sprintf('Found invalid visibility %s', $visibility)
            )
        );
    }

    /**
     * Pre-load the entity ID for the passed product.
     *
     * @param array $product The product to be pre-loaded
     *
     * @return void
     */
    public function preLoadEntityId(array $product)
    {
        $this->preLoadedEntityIds[$product[MemberNames::SKU]] = $product[MemberNames::ENTITY_ID];
    }

    /**
     * Return's the entity type for the configured entity type code.
     *
     * @return array The requested entity type
     * @throws \Exception Is thrown, if the requested entity type is not available
     */
    public function getEntityType()
    {

        // query whether or not the entity type with the passed code is available
        if (isset($this->entityTypes[$entityTypeCode = $this->getEntityTypeCode()])) {
            return $this->entityTypes[$entityTypeCode];
        }

        // throw a new exception
        throw new \Exception(
            $this->appendExceptionSuffix(
                sprintf('Requested entity type "%s" is not available', $entityTypeCode)
            )
        );
    }

    /**
     * Return's TRUE, if the passed URL key varchar value IS related with the actual PK.
     *
     * @param array $productVarcharAttribute The varchar value to check
     *
     * @return boolean TRUE if the URL key is related, else FALSE
     */
    public function isUrlKeyOf(array $productVarcharAttribute)
    {
        return ((integer) $productVarcharAttribute[MemberNames::ENTITY_ID] === (integer) $this->getLastEntityId()) &&
               ((integer) $productVarcharAttribute[MemberNames::STORE_ID] === (integer) $this->getRowStoreId(StoreViewCodes::ADMIN));
    }
}
