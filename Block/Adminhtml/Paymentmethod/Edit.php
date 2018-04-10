<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */
 
namespace Icepay\IcpCore\Block\Adminhtml\Paymentmethod;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Payment method edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'paymentmethod_id';
        $this->_blockGroup = 'Icepay_IcpCore';
        $this->_controller = 'adminhtml_paymentmethod';

        parent::_construct();

        if ($this->_isAllowedAction('Icepay_IcpCore::paymentmethod_save')) {
            $this->buttonList->update('save', 'label', __('Save Payment Method'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

    }

    /**
     * Get header with Payment Method name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('paymentmethod')->getId()) {
            return __("Edit payment method '%1'", $this->escapeHtml($this->_coreRegistry->registry('paymentmethod')->getName()));
        } else {
            return __('New payment method');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('paymentmethod/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }
}