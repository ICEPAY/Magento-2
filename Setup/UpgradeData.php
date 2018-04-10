<?php

namespace Icepay\IcpCore\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use \Magento\Sales\Model\Order;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.2') < 0) {

            //NEW
            $select = $setup->getConnection()->select()
                ->from(
                    $setup->getTable('sales_order_status'), ['status',])
                ->where('status = ?', 'icepay_icpcore_new');

            if (count($setup->getConnection()->fetchAll($select)) == 0) {
                $setup->getConnection()->insert(
                    $setup->getTable('sales_order_status'),
                    [
                        'status' => 'icepay_icpcore_new',
                        'label'  => __('New'),
                    ]
                );
                $setup->getConnection()->insert(
                    $setup->getTable('sales_order_status_state'),
                    [
                        'status'           => 'icepay_icpcore_new',
                        'state'            => Order::STATE_NEW,
                        'is_default'       => 0,
                        'visible_on_front' => 1,
                    ]
                );
            }

            //OPEN
            $select = $setup->getConnection()->select()
                ->from(
                    $setup->getTable('sales_order_status'), ['status',])
                ->where('status = ?', 'icepay_icpcore_open');

            if (count($setup->getConnection()->fetchAll($select)) == 0) {
                $setup->getConnection()->insert(
                    $setup->getTable('sales_order_status'),
                    [
                        'status' => 'icepay_icpcore_open',
                        'label'  => __('Awaiting payment'),
                    ]
                );
                $setup->getConnection()->insert(
                    $setup->getTable('sales_order_status_state'),
                    [
                        'status'           => 'icepay_icpcore_open',
                        'state'            => Order::STATE_PENDING_PAYMENT,
                        'is_default'       => 0,
                        'visible_on_front' => 1,
                    ]
                );
            }

            //OK
            $select = $setup->getConnection()->select()
                ->from(
                    $setup->getTable('sales_order_status'), ['status',])
                ->where('status = ?', 'icepay_icpcore_ok');

            if (count($setup->getConnection()->fetchAll($select)) == 0) {
                $setup->getConnection()->insert(
                    $setup->getTable('sales_order_status'),
                    [
                        'status' => 'icepay_icpcore_ok',
                        'label'  => __('Payment received'),
                    ]
                );
                $setup->getConnection()->insert(
                    $setup->getTable('sales_order_status_state'),
                    [
                        'status'           => 'icepay_icpcore_ok',
                        'state'            => Order::STATE_PROCESSING,
                        'is_default'       => 0,
                        'visible_on_front' => 1,
                    ]
                );
            }

            //ERROR
            $select = $setup->getConnection()->select()
                ->from(
                    $setup->getTable('sales_order_status'), ['status',])
                ->where('status = ?', 'icepay_icpcore_error');

            if (count($setup->getConnection()->fetchAll($select)) == 0) {
                $setup->getConnection()->insert(
                    $setup->getTable('sales_order_status'),
                    [
                        'status' => 'icepay_icpcore_error',
                        'label'  => __('Payment error'),
                    ]
                );
                $setup->getConnection()->insert(
                    $setup->getTable('sales_order_status_state'),
                    [
                        'status'           => 'icepay_icpcore_error',
                        'state'            => Order::STATE_CANCELED,
                        'is_default'       => 0,
                        'visible_on_front' => 1,
                    ]
                );
            }

        }

        $setup->endSetup();

    }


}