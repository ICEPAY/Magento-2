<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */
 
namespace Icepay\IcpCore\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $paymentmethodTable = \Icepay\IcpCore\Model\Paymentmethod::ENTITY;
        $table = $setup->getConnection()
            ->newTable($setup->getTable($paymentmethodTable))
            ->addColumn(
                'paymentmethod_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Payment Method ID'
            )
            ->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                16,
                ['nullable' => false],
                'Payment Method Code'
            )
            ->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Payment Method Active'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Website ID'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Payment Method Name'
            )
            ->addColumn(
                'display_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Payment Method Display Name'
            )
            ->addColumn(
                'display_position',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Payment Method Display Position'
            )
            ->addColumn(
                'raw_pm_data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Raw Payment Method Data (serialized array)'
            )
            ->addIndex(
                $setup->getIdxName(
                    $paymentmethodTable,
                    ['code', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['code', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $setup->getIdxName($paymentmethodTable, ['code']),
                ['code']
            )
            ->addIndex(
                $setup->getIdxName($paymentmethodTable, ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $setup->getFkName($paymentmethodTable, 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName($paymentmethodTable, 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Icepay Payment Methods');

        $installer->getConnection()->createTable($table);


        /**
         * Create table 'icepay_icpcore_issuer'
         */
        $issuerTable = \Icepay\IcpCore\Model\Issuer::ENTITY;
        $table = $setup->getConnection()
            ->newTable($setup->getTable($issuerTable))
            ->addColumn(
                'issuer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Issuer ID'
            )
            ->addColumn(
                'paymentmethod_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Payment Method ID'
            )
            ->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                16,
                ['nullable' => false],
                'Issuer Code'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Issuer Name'
            )
            ->addColumn(
                'country_list',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Issuer countries (serialized array)'
            )
            ->addColumn(
                'currency_list',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Issuer currencies (serialized array)'
            )
            ->addColumn(
                'language_list',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Issuer languages (serialized array)'
            )
            ->addColumn(
                'minimum_amount_list',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Issuer Minimum Order Amount (serialized array)'
            )
            ->addColumn(
                'maximum_amount_list',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Issuer Maximum Order Amount (serialized array)'
            )
//            ->addColumn(
//                'minimum_amount',
//                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
//                '12,4',
//                ['nullable' => false, 'default' => '0.0000'],
//                'Issuer Minimum Order Amount'
//            )
//            ->addColumn(
//                'maximum_amount',
//                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
//                '12,4',
//                ['nullable' => false, 'default' => '0.0000'],
//                'Issuer Maximum Order Amount'
//            )
            ->addIndex(
                $setup->getIdxName($issuerTable, ['code']),
                ['code']
            )
            ->addForeignKey(
                $installer->getFkName($issuerTable, 'paymentmethod_id', $paymentmethodTable, 'paymentmethod_id'),
                'paymentmethod_id',
                $installer->getTable($paymentmethodTable),
                'paymentmethod_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}