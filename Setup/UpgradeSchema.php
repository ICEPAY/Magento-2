<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\TestFramework\Inspection\Exception;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $paymentmethodTable = \Icepay\IcpCore\Model\Paymentmethod::ENTITY;
        $issuerTable = \Icepay\IcpCore\Model\Issuer::ENTITY;

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $connection = $setup->getConnection();


            $connection->dropForeignKey(
                $setup->getTable($paymentmethodTable),
                $setup->getFkName(
                    $issuerTable,
                    'paymentmethod_id',
                    $paymentmethodTable,
                    'paymentmethod_id'
                )
            );

            $connection->addForeignKey(
                $setup->getFkName($issuerTable, 'paymentmethod_id', $paymentmethodTable, 'paymentmethod_id'),
                $setup->getTable($issuerTable),
                'paymentmethod_id',
                $setup->getTable($paymentmethodTable),
                'paymentmethod_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        }

        $setup->endSetup();

    }
}