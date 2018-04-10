<?php
/**
 * @package       ICEPAY Magento 2 Payment Module
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see LICENSE.md
 */
 
namespace Icepay\IcpCore\Model;

use Icepay\IcpCore\Api\Data;
use Icepay\IcpCore\Api\PaymentmethodRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Icepay\IcpCore\Model\ResourceModel\Paymentmethod as ResourcePaymentmethod;
use Icepay\IcpCore\Model\ResourceModel\Paymentmethod\CollectionFactory as PaymentmethodCollectionFactory;

/**
 * Class BlockRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentmethodRepository implements PaymentmethodRepositoryInterface
{
    /**
     * @var ResourcePaymentmethod
     */
    protected $resource;

    /**
     * @var PaymentmethodFactory
     */
    protected $paymentmethodFactory;

    /**
     * @var Data\BlockSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Icepay\IcpCore\Api\Data\PaymentmethodInterfaceFactory
     */
    protected $dataPaymentmethodFactory;
    

    /**
     * @param ResourcePaymentmethod\ $resource
     * @param PaymentmethodFactory $paymentmethodFactory
     * @param Data\PaymentmethodInterfaceFactory $dataPaymentmethodFactory
     * @param PaymentmethodCollectionFactory $paymentmethodCollectionFactory
     * @param Data\BlockSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
          ResourcePaymentmethod $resource,
          PaymentmethodFactory $paymentmethodFactory,
        \Icepay\IcpCore\Api\Data\PaymentmethodInterfaceFactory $dataPaymentmethodFactory,
        PaymentmethodCollectionFactory $paymentmethodCollectionFactory,
        Data\PaymentmethodSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    )
    {
        $this->resource = $resource;
        $this->paymentmethodFactory = $paymentmethodFactory;
        $this->paymentmethodCollectionFactory = $paymentmethodCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPaymentmethodFactory = $dataPaymentmethodFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }


    /**
     * Save Payment Method data
     *
     * @param \Icepay\IcpCore\Api\Data\PaymentmethodInterface $paymentmethod
     * @return Paymentmethod
     * @throws CouldNotSaveException
     */
    public function save(\Icepay\IcpCore\Api\Data\PaymentmethodInterface $paymentmethod)
    {
        try {
            $this->resource->save($paymentmethod);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $paymentmethod;
    }


    /**
     * Load payment method data by given ID
     *
     * @param string $paymentmethodId
     * @return Paymentmethod
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($paymentmethodId)
    {
        $method = $this->paymentmethodFactory->create();
        $this->resource->load($method, $paymentmethodId);
        if (!$method->getId()) {
            throw new NoSuchEntityException(__('Payment method with id "%1" does not exist.', $paymentmethodId));
        }
        return $method;
    }


    /**
     * Load Payment Method data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Magento\Catalog\Api\Data\PaymentmethodInterface[]
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->paymentmethodCollectionFactory->create();

        //Add filters from root filter group to the collection
        /** @var FilterGroup $group */
        foreach ($criteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Icepay\IcpCore\Model\ResourceModel\Paymentmethod\Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }


    /**
     * Delete Payment Method
     *
     * @param \Icepay\IcpCore\Api\Data\PaymentmethodInterface $paymentmethod
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Icepay\IcpCore\Api\Data\PaymentmethodInterface $paymentmethod)
    {
        try {
            $this->resource->delete($paymentmethod);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Payment Method by given ID
     *
     * @param int $paymentmethodId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($paymentmethodId)
    {
        return $this->delete($this->getById($paymentmethodId));
    }

}