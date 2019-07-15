<?php
/**
 * NotificationAlerts observer
 *
 * @category   MediaLounge
 * @package    MediaLounge_UniqueUrlProducts
 * @author     Mario SAM <eu@mariosam.com.br>
 */
class MediaLounge_UniqueUrlProducts_Model_Observer
{
	//randomic messages errors
	protected $_msgs = array(
			'1 The value of attribute "%s" must be unique'
			,'2 The value of attribute "%s" must be unique'
			,'3 The value of attribute "%s" must be unique'
			,'4 The value of attribute "%s" must be unique'
			,'5 The value of attribute "%s" must be unique');
	
	/**
	 * http://doc4dev.net/doc/Magento/1/class-Mage_ImportExport_Model_Product_Attribute_Backend_Urlkey.html
	 */
	public function importValidate(Varien_Event_Observer $observer = null) 
	{
		//this observer can not be null
		if ($observer == null)
			Mage::log('Titanic Sank!');
		
		//if this is the right observer (import before save event)
		if ($observer->getEvent()->getName() == "catalog_product_import_finish_before") {
			Mage::log(var_export($observer->debug(), TRUE));
		}
		
		Mage::log('FIM import Validate');
	}

	/**
	 * Intercept the validation of atributes BEFORE 
	 *  
	 * @param Varien_Event_Observer $observer
	 * @return boolean
	 */
	public function beforeValidate(Varien_Event_Observer $observer = null)
	{		
		//this observer can not be null
		if ($observer == null)
			Mage::log('Titanic Sank!');

		//if this is the right observer (before event)
		$event = $observer->getEvent()->getName();
		if ($event == "catalog_product_validate_before") {
			//recovery values of attributes product 
			$product = $observer->getEvent()->getProduct();
			$urlkey  = $product->getUrlKey();

			//what I need to do if is blank???
			if ($urlkey == "" || $urlkey == null) {
				//Mage::log('Nenhum valor informado em Url_Key');
				return false;
			}

			//load type of table to consult url_key
			$entity    = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY);
			$attribute = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY,'url_key');
			$attribute->setEntity($entity);

			//verify if the url_key is unique
			if (!$entity->checkAttributeUniqueValue($attribute, $product)) {
				//prepare error msg
				$label      = $attribute->getFrontend()->getLabel();
				$messages[] = Mage::helper('eav')->__('The value of attribute "%s" must be unique', $label);
				$massage    = array_shift($messages);
				
				//throw the error msg to screen
				$eavExc = new Mage_Eav_Model_Entity_Attribute_Exception($massage);
				$eavExc->setAttributeCode( $attribute->getName() );
				throw $eavExc;
			} else {
				//Mage::log('A url "%s" nao existe na base de dados',$urlkey);
			}
		}

		//Mage::log(var_export($observer->debug(), TRUE));
		return true;
	}
	
    /**
     * Intercept the validation of atributes AFTER
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterValidate(Varien_Event_Observer $observer)
    {	
		//this observer can not be null
		if ($observer == null)
			Mage::log('Titanic Sank!');

		//if this is the right observer (before event)
		if ( "catalog_product_validate_after"==$observer->getEvent()->getName() ) {
			//recovery values of attributes product 
			$product = $observer->getEvent()->getProduct();
			$urlkey  = $product->getUrlKey();

			//what I need to do if is blank???
			if ($urlkey == "" || $urlkey == null) {
				Mage::log('Nenhum valor informado em Url_Key');
				$urlkey = str_replace(" ","-",trim(strtolower( $product->getName() )));
				$product->setUrlKey( $urlkey );
				//return false;
			}

			//load type of table to consult url_key
			$entity    = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY);
			$attribute = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY,'url_key');
			$attribute->setEntity($entity);

			//verify if the url_key is unique
			if (!$entity->checkAttributeUniqueValue($attribute, $product)) {
				//prepare error msg
				$label      = $attribute->getFrontend()->getLabel();
				$msg        = $this->_msgs[array_rand( $this->_msgs )];

				$messages[] = Mage::helper('eav')->__($msg, $label);
				$massage    = array_shift($messages);
				
				//throw the error msg to screen
				$eavExc = new Mage_Eav_Model_Entity_Attribute_Exception($massage);
				$eavExc->setAttributeCode( $attribute->getName() );
				throw $eavExc;
			} else {
				//Mage::log('A url "%s" nao existe na base de dados',$urlkey);
			}
		}

		//Mage::log(var_export($observer->debug(), TRUE));
		return true;
    }
}
