<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Block\Adminhtml\Paymentmethod\Edit;

use \Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('paymentmethod_form');
        $this->setTitle(__('Payment Method Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Icepay\IcpCore\Model\Paymentmethod $model */
        $model = $this->_coreRegistry->registry('paymentmethod');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('paymentmethod_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Edit Payment Method'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('paymentmethod_id', 'hidden', ['name' => 'id']);

            $fieldset->addField(
                'display_name',
                'text',
                ['name' => 'display_name', 'label' => __('Display Name'), 'title' => __('Display Name'), 'required' => true]
            );

            $fieldset->addField(
                'is_active',
                'select',
                [
                    'label' => __('Status'),
                    'title' => __('Status'),
                    'name' => 'is_active',
                    'required' => true,
                    'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
                ]
            );
        }


        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}