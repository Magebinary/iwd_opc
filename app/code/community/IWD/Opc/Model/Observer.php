<?php
class IWD_Opc_Model_Observer{
	
	public function checkRequiredModules($observer){
		$cache = Mage::app()->getCache();
		
	}
	
	
	
	public function newsletter($observer){
		$_session = Mage::getSingleton('core/session');

		$newsletterFlag = $_session->getIsSubscribed();
		if ($newsletterFlag==true){
			
			$email = $observer->getEvent()->getOrder()->getCustomerEmail();
			
			$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
	        if($subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED && $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
	            $subscriber->setImportMode(true)->subscribe($email);
	            
	            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
	            $subscriber->sendConfirmationSuccessEmail();
	        }
			
		}
		
	}
	
	public function applyComment($observer){
		$order = $observer->getData('order');
		
		$comment = Mage::getSingleton('core/session')->getOpcOrderComment();
		if (!Mage::helper('opc')->isShowComment() || empty($comment)){
			return;
		}
		try{
			$order->setCustomerComment($comment);
			$order->setCustomerNoteNotify(true);
			$order->setCustomerNote($comment);
			$order->addStatusHistoryComment($comment)->setIsVisibleOnFront(true)->setIsCustomerNotified(true);
			$order->save();
			$order->sendOrderUpdateEmail(true, $comment);
		}catch(Exception $e){
			Mage::logException($e);
		}
	}

    public function checkoutCartAddProductComplete($observer){
        if (!Mage::getStoreConfig('payment/incontext/enable')){
            return;
        }

        $request = $observer->getRequest();
        $response = $observer->getRequest();
        $returnUrl = $request->getParam('return_url', false);
        if (preg_match('/express\/start/i', $returnUrl)){
            $request->setParam('return_url', Mage::getUrl('checkout/cart', array('_secure'=>true)));
        }
    }

}