<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog products compare block
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product\Compare;

class ListCompare extends \Magento\Catalog\Block\Product\Compare\AbstractCompare
{
    /**
     * Product Compare items collection
     *
     * @var \Magento\Catalog\Model\Resource\Product\Compare\Item\Collection
     */
    protected $_items;

    /**
     * Compare Products comparable attributes cache
     *
     * @var array
     */
    protected $_attributes;

    /**
     * Flag which allow/disallow to use link for as low as price
     *
     * @var bool
     */
    protected $_useLinkForAsLowAs = false;

    /**
     * Customer id
     *
     * @var null|int
     */
    protected $_customerId = null;

    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp_noform';

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Log visitor
     *
     * @var \Magento\Log\Model\Visitor
     */
    protected $_logVisitor;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Item collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory
     */
    protected $_itemCollectionFactory;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishlistHelper;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Catalog\Helper\Product\Compare $compareProduct
     * @param \Magento\Theme\Helper\Layout $layoutHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Log\Model\Visitor $logVisitor
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     * @param array $priceBlockTypes
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Core\Model\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Catalog\Helper\Product\Compare $compareProduct,
        \Magento\Theme\Helper\Layout $layoutHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Log\Model\Visitor $logVisitor,
        \Magento\Customer\Model\Session $customerSession,
        array $data = array(),
        array $priceBlockTypes = array()
    ) {
        $this->_wishlistHelper = $wishlistHelper;
        $this->_coreData = $coreData;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_logVisitor = $logVisitor;
        $this->_customerSession = $customerSession;
        parent::__construct(
            $context,
            $catalogConfig,
            $registry,
            $taxData,
            $catalogData,
            $mathRandom,
            $cartHelper,
            $wishlistHelper,
            $compareProduct,
            $layoutHelper,
            $imageHelper,
            $data,
            $priceBlockTypes
        );
        $this->_isScopePrivate = true;
    }

    /**
     * Get add to wishlist params
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAddToWishlistParams($product)
    {
        $continueUrl    = $this->_coreData->urlEncode($this->getUrl('customer/account'));
        $urlParamName   = \Magento\App\Action\Action::PARAM_NAME_URL_ENCODED;

        $continueUrlParams = array($urlParamName => $continueUrl);

        return $this->_wishlistHelper->getAddParams($product, $continueUrlParams);
    }

    /**
     * Preparing layout
     *
     * @return \Magento\Catalog\Block\Product\Compare\ListCompare
     */
    protected function _prepareLayout()
    {
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(__('Products Comparison List') . ' - ' . $headBlock->getDefaultTitle());
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrieve Product Compare items collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Compare\Item\Collection
     */
    public function getItems()
    {
        if (is_null($this->_items)) {
            $this->_compareProduct->setAllowUsedFlat(false);

            $this->_items = $this->_itemCollectionFactory->create();
            $this->_items->useProductItem(true)
                ->setStoreId($this->_storeManager->getStore()->getId());

            if ($this->_customerSession->isLoggedIn()) {
                $this->_items->setCustomerId($this->_customerSession->getCustomerId());
            } elseif ($this->_customerId) {
                $this->_items->setCustomerId($this->_customerId);
            } else {
                $this->_items->setVisitorId($this->_logVisitor->getId());
            }

            $this->_items
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->loadComparableAttributes()
                ->addMinimalPrice()
                ->addTaxPercents()
                ->setVisibility($this->_catalogProductVisibility->getVisibleInSiteIds());
        }

        return $this->_items;
    }

    /**
     * Retrieve Product Compare Attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        if (is_null($this->_attributes)) {
            $this->_attributes = $this->getItems()->getComparableAttributes();
        }

        return $this->_attributes;
    }

    /**
     * Retrieve Product Attribute Value
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @return string
     */
    public function getProductAttributeValue($product, $attribute)
    {
        if (!$product->hasData($attribute->getAttributeCode())) {
            return __('N/A');
        }

        if ($attribute->getSourceModel()
            || in_array($attribute->getFrontendInput(), array('select','boolean','multiselect'))
        ) {
            //$value = $attribute->getSource()->getOptionText($product->getData($attribute->getAttributeCode()));
            $value = $attribute->getFrontend()->getValue($product);
        } else {
            $value = $product->getData($attribute->getAttributeCode());
        }
        return ((string)$value == '') ? __('No') : $value;
    }

    /**
     * Retrieve Print URL
     *
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('*/*/*', array('_current'=>true, 'print'=>1));
    }

    /**
     * Setter for customer id
     *
     * @param int $id
     * @return \Magento\Catalog\Block\Product\Compare\ListCompare
     */
    public function setCustomerId($id)
    {
        $this->_customerId = $id;
        return $this;
    }
}
