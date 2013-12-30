<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Import;

/**
 * Validates imported JobInstance
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceImportValidator extends ImportValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getIdentifier(array $columnsInfo, $entity)
    {
        return $entity->getCode();
    }
}