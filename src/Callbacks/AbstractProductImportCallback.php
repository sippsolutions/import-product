<?php

/**
 * TechDivision\Import\Product\Callbacks\AbstractProductImportCallback
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

namespace TechDivision\Import\Product\Callbacks;

use TechDivision\Import\Product\Utils\ColumnKeys;
use TechDivision\Import\Callbacks\AbstractCallback;

/**
 * Abstract product import callback implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product
 * @link      http://www.techdivision.com
 */
abstract class AbstractProductImportCallback extends AbstractCallback implements ProductImportCallbackInterface
{

    /**
     * Return's the product SKU as unique identifier of the actual row.
     *
     * @return mixed The row's unique identifier
     */
    protected function getUniqueIdentifier()
    {
        return $this->getValue(ColumnKeys::SKU);
    }
}
