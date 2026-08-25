<?php
namespace Custom\ProductList\Controller\Ajax\Load;

/**
 * Interceptor class for @see \Custom\ProductList\Controller\Ajax\Load
 */
class Interceptor extends \Custom\ProductList\Controller\Ajax\Load implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Framework\App\RequestInterface $request, \Magento\Framework\View\Result\PageFactory $pageFactory, \Custom\ProductList\Block\ProductList $productList)
    {
        $this->___init();
        parent::__construct($resultJsonFactory, $request, $pageFactory, $productList);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute();
    }
}
