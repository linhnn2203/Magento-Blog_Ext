<?php

namespace Smartosc\Sumup\Block\Blog;

use Magento\Backend\Block\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Smartosc\Sumup\Controller\Router;
use Smartosc\Sumup\Model\BlogFactory;
use Smartosc\Sumup\Model\Config;
use Smartosc\Sumup\Model\ResourceModel\Blog\CollectionFactory;
use Smartosc\Sumup\Model\Url;

class BlogList extends Template
{
    private $_blogFactory;

    private $scopeConfig;

    private $blogCollectionFactory;

    private $url;

    protected $_storeManager;

    protected $_urlInterface;

    protected $_urlRewriteFactory;

    public function __construct(
        BlogFactory $blogFactory,
        Config $config,
        Registry $registry,
        Template\Context $context,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $blogCollectionFactory,
        Url $url,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\UrlRewrite\Model\UrlRewriteFactory  $urlRewriteFactory
    ) {
        $this->_urlRewriteFactory = $urlRewriteFactory;
        $this->url = $url;
        $this->_blogFactory = $blogFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        $this->blogCollectionFactory = $blogCollectionFactory;
        parent::__construct($context);
    }

    public function action()
    {
        $connection = $this->blogCollectionFactory->create()->getConnection();
        $select = $connection->select()->from($this->blogCollectionFactory->create()->getTable('sumup_blog'));

        $result = $connection->fetchAll($select);
        return $result;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Blog List'));
        //  if ($this->getCustomCollection()) {
        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'custom.history.pager'
        )->setAvailableLimit([1 => 1, 5 => 5, 10 => 10, 15 => 15, 20 => 20])
            ->setShowPerPage(true)->setCollection(
                $this->getCustomCollection()
            );
        $this->setChild('pager', $pager);
        // $this->getCustomCollection()->load();
        // }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    public function getCustomCollection()
    {
        $collection = $this->blogCollectionFactory->create();

        $searchblog = ($this->getRequest()->getParam('q')) ? $this->getRequest()->getParam('q') : "";
        if (!empty($searchblog)) {
            $collection->addFieldToFilter('name', ['like' => '%' . $searchblog . '%']);
        }
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 1;

        $collection->addFieldToFilter('status_id', '1');
        date_default_timezone_set("Asia/Ho_Chi_Minh");
        $current_date = date("Y-m-d H:i:s");

        $collection->addFieldToFilter('publish_date_from', ['lteq'=>$current_date]);
        $collection->addFieldToFilter('publish_date_to', ['gteq'=>$current_date]);

        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        return $collection;
    }

    public function getConfig()
    {
        $value = $this->scopeConfig->getValue("sumup/general/enable");
        return $value;
    }

    public function getSearchUrl()
    {
        return $this->_storeManager->getStore()->getUrl('sumup/index/bloglist');
    }

    public function getBlogUrl()
    {
        return $this->_storeManager->getStore()->getUrl('sumup/index/blogdetail');
    }

    public function getPostUrl($post)
    {
        $suffix = $this->_scopeConfig->getValue('managepost/general/suffix');
        return $this->getUrl() . Router::BLOG_ROUTER . '/' . $post->getUrlKey() . $suffix;
    }
}
