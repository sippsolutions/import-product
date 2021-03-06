<?php

/**
 * TechDivision\Import\Product\Repositories\ProductRepository
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

namespace TechDivision\Import\Product\Repositories;

use TechDivision\Import\Product\Utils\CacheKeys;
use TechDivision\Import\Product\Utils\MemberNames;
use TechDivision\Import\Product\Utils\SqlStatementKeys;
use TechDivision\Import\Repositories\AbstractFinderRepository;
use TechDivision\Import\Connection\ConnectionInterface;
use TechDivision\Import\Repositories\SqlStatementRepositoryInterface;
use TechDivision\Import\Repositories\Finders\FinderFactoryInterface;
use TechDivision\Import\Utils\PrimaryKeyUtilInterface;

/**
 * Repository implementation to load product data.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product
 * @link      http://www.techdivision.com
 */
class ProductRepository extends AbstractFinderRepository implements ProductRepositoryInterface
{

    /**
     * The primary key utility instance.
     *
     * @var \TechDivision\Import\Utils\PrimaryKeyUtilInterface
     */
    protected $primaryKeyUtil;

    /**
     * Initialize the repository with the passed connection and utility class name.
     * .
     * @param \TechDivision\Import\Connection\ConnectionInterface               $connection             The connection instance
     * @param \TechDivision\Import\Repositories\SqlStatementRepositoryInterface $sqlStatementRepository The SQL repository instance
     * @param \TechDivision\Import\Repositories\Finders\FinderFactoryInterface  $finderFactory          The finder factory instance
     * @param \TechDivision\Import\Utils\PrimaryKeyUtilInterface                $primaryKeyUtil         The primary key utility instance
     */
    public function __construct(
        ConnectionInterface $connection,
        SqlStatementRepositoryInterface $sqlStatementRepository,
        FinderFactoryInterface $finderFactory,
        PrimaryKeyUtilInterface $primaryKeyUtil
    ) {

        // set the primary key utility instance
        $this->primaryKeyUtil = $primaryKeyUtil;

        // pass the connection, SQL statement repository and the primary key utility to the parent class
        parent::__construct($connection, $sqlStatementRepository, $finderFactory);
    }

    /**
     * Initializes the repository's prepared statements.
     *
     * @return void
     */
    public function init()
    {

        // initialize the prepared statements
        $this->addFinder($this->finderFactory->createFinder($this, SqlStatementKeys::PRODUCT));
        $this->addFinder($this->finderFactory->createFinder($this, SqlStatementKeys::PRODUCTS));
    }

    /**
     * Return's the finder's entity name.
     *
     * @return string The finder's entity name
     */
    public function getEntityName()
    {
        return CacheKeys::PRODUCT;
    }

    /**
     * Return's the primary key name of the entity.
     *
     * @return string The name of the entity's primary key
     */
    public function getPrimaryKeyName()
    {
        return $this->primaryKeyUtil->getPrimaryKeyMemberName();
    }

    /**
     * Return's the entity unique key name.
     *
     * @return string The name of the entity's unique key
     */
    public function getUniqueKeyName()
    {
        return MemberNames::SKU;
    }

    /**
     * Return's the available products.
     *
     * @return array The available products
     */
    public function findAll()
    {
        foreach ($this->getFinder(SqlStatementKeys::PRODUCTS)->find() as $result) {
            yield $result;
        }
    }

    /**
     * Return's the product with the passed SKU.
     *
     * @param string $sku The SKU of the product to return
     *
     * @return array|null The product
     */
    public function findOneBySku($sku)
    {
        return $this->getFinder(SqlStatementKeys::PRODUCT)->find(array($this->getUniqueKeyName() => $sku));
    }
}
