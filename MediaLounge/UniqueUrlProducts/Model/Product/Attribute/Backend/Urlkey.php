<?php
/**
 * Product url key attribute backend OVERRIDE
 *
 * @category   MediaLounge
 * @package    MediaLounge_UniqueUrlProducts
 * @author     Mario SAM <eu@mariosam.com.br>
 */
class MediaLounge_UniqueUrlProducts_Model_Product_Attribute_Backend_Urlkey extends Mage_Catalog_Model_Product_Attribute_Backend_Urlkey
{
	//randomic messages errors
	protected $_msgs = array(
			'The value of attribute "%s" must be unique',
			'You know, the value "%s" must be unique',
			'Please, this value already exist, choose another "%s"',
			'You need change the value of attribute "%s", this one alreay exist',
			'This "%s" must be unique, you know that');

	/**
	 * Override method to call customized validate while edite/create data product
	 * 
	 * @see Mage_Eav_Model_Entity_Attribute_Backend_Abstract::validate()
	 */
	public function validate($object)
	{
		$this->getAttribute()->setData("is_unique",true);
		Mage::log("1");

		parent::validate($object);

		//Mage::log('eh unico? >> '.var_export($this->getAttribute()->getData("is_unique"),true));
		
		//$this->_validateUrlKey($object);

		//return true;
	}

	/**
	 * Override method to call customized validate before save data product
	 * 
	 * @see Mage_Catalog_Model_Product_Attribute_Backend_Urlkey::beforeSave()
	 */
	public function beforeSave($object)
	{
		//$this->getAttribute()->setData("is_unique",true);
		//$object->setData("is_unique",true);

		Mage::log("0");
		parent::beforeSave($object);

		//if is import action ignore validation
		if ("Mage_ImportExport_Model_Import_Proxy_Product"==get_class($object)) {
			Mage::log("consegui chamar a classe URL Key pelo import de produtos");
			return $this;
		}

		//if the user click in duplicate button, dont need validate
		//if (!$object->getData('is_duplicate')) {
			//$this->_validateUrlKey($object);
		//}

		//return $this;
	}

	/**
	 * Customized method to validate attribute URL_Key (admin form edit or new product)
	 * 
	 * @param Product $object
	 * @return self|throw|boolean
	 */
	protected function _validateUrlKey($object)
	{
		Mage::log("3");
		$attrCode = $this->getAttribute()->getAttributeCode();
		$value    = $object->getData($attrCode);

		if ($value === false) {
			return $this;
		}

		//if url_key is not unique value
		if ($value != '') {
			if (!$this->getAttribute()->getEntity()->checkAttributeUniqueValue($this->getAttribute(), $object)) {
				$label = $this->getAttribute()->getFrontend()->getLabel();
				$msg   = $this->_msgs[array_rand( $this->_msgs )];

				throw Mage::exception('Mage_Eav',Mage::helper('eav')->__($msg, $label));
			}
		}

		return true;
	}
}
