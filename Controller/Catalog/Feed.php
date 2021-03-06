<?php
namespace InnovateOne\EshopsWithIQ\Controller\Catalog;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use InnovateOne\EshopsWithIQ\Model\ProductFeed;


class Feed extends Action
{
	/**
		* @var RawFactory
	*/
	private $rawResultFactory;
	
	/**
		* @var \InnovateOne\ProductFeed\Model\ProductFeed
	*/
	private $productFeed;
	
	public function __construct(
	Context $context,
	ProductFeed $productFeed,
	RawFactory $rawResultFactory,
	\Magento\Framework\App\Request\Http $request
	) {
		$this->request = $request;
		
		if ($this->request->getParam('eswiq_debug')) {
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(E_ALL);
		}
		
		$this->rawResultFactory = $rawResultFactory;
		$this->productFeed = $productFeed;
		return parent::__construct($context);
	}
	
	public function execute() {
		$result = $this->rawResultFactory->create();
		try {
			if (!$this->request->getParam('raw')) {
				$result->setHeader('Content-Type', 'application/xml; charset=utf-8');
				} else {
				$result->setHeader('Content-Type', 'application/json; charset=utf-8');
			}
			
			$contents =  $this->productFeed->getContent($this->request);
			$result->setContents($contents);
			} catch(\Exception $ex) {
			$result->setContents($ex->getMessage());
		}
		return $result;
	}
}
