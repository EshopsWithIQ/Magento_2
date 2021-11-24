<?php
namespace InnovateOne\EshopsWithIQ\Block;

use Magento\Framework\App\ObjectManager;
use InnovateOne\EshopsWithIQ\Model\Helper;

class Head extends \Magento\Framework\View\Element\Template
{
	public $product_id;
	
    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = [],
		Helper $helper
	) {
		
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');//get current product
		if ($product) $this->product_id = $product->getId();
		
		//session
		$session_id = isset($_SESSION['eshopswithiq']) ? $_SESSION['eshopswithiq'] : null;
		if (empty($session_id)) $session_id = isset($_COOKIE['eshopswithiq']) ? $_COOKIE['eshopswithiq'] : null;
		
		//server side fallback
		$post_data = [];
		if (!empty($_GET['eclid'])) {
			$post_data['lead'] = $_GET['eclid'];
		} else if (!empty($_GET['ea_client']) && !empty($_GET['ea_channel'])) {
			$lead_data = ['client' => $_GET['ea_client'], 'channel' => $_GET['ea_channel']];
			if (!empty($_GET['ea_group'])) $lead_data['group'] = $_GET['ea_group'];
			if (!empty($_GET['ea_product'])) $lead_data['product'] = $_GET['ea_product'];
			$post_data['lead'] = base64_encode(json_encode($lead_data));
		}
		if ($this->product_id) {
			$post_data['page_data']['product_id'] = $this->product_id;
		}
		if (count($post_data) || empty($session_id)) {
			$post_data['session_id'] = $session_id;
			$response = $helper->call('http://cts.eshopswithiq.com/v', $post_data);
			if ($response) {
				$session_id = $response;
				$_SESSION['eshopswithiq'] = $session_id;
			}
		}
		if (empty($_COOKIE['eshopswithiq']) && !empty($session_id)) {
			//setcookie('eshopswithiq', $session_id, time() + (86400 * 30), "/");
			header('Set-Cookie: eshopswithiq='.$session_id.'; expires='.(time() + (86400 * 30)).'; path=/; SameSite=None; Secure');
		}
		
		parent::__construct($context, $data);
    }
}