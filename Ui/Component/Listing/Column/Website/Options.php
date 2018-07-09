<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */

namespace Icepay\IcpCore\Ui\Component\Listing\Column\Website;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store as SystemStore;

class Options implements OptionSourceInterface
{

    /**
     * All Store Views value
     */
    const ALL_WEBSITES = '0';

    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * Constructor
     *
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     */
    public function __construct(SystemStore $systemStore, Escaper $escaper)
    {
        $this->systemStore = $systemStore;
        $this->escaper = $escaper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $currentOptions['']['label'] = '--';
        $currentOptions['']['value'] = '--';

        $currentOptions['All Store Views']['label'] = __('All Websites');
        $currentOptions['All Store Views']['value'] = self::ALL_WEBSITES;

        $websiteCollection = $this->systemStore->getWebsiteCollection();

        foreach ($websiteCollection as $website) {
            $name = $this->escaper->escapeHtml($website->getName());
            $currentOptions[$name]['label'] = $name;
            $currentOptions[$name]['value'] = $website->getId();
        }

        $this->options = array_values($currentOptions);

        return $currentOptions;
    }
}
