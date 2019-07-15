<?php
/**
 * Import entity simple product type OVERRIDE
 *
 * @category   MediaLounge
 * @package    MediaLounge_UniqueUrlProducts
 * @author     Mario SAM <eu@mariosam.com.br>
 */
class MediaLounge_UniqueUrlProducts_Model_Import_Entity_Product_Type_Simple extends Mage_ImportExport_Model_Import_Entity_Product_Type_Simple
{
	//save the all url in process to import (to check duplicate entries)
	protected $_list_urlkey = array();

	/**
	 * OVERRIDE method to call checkUrlToChange
	 * 
	 * @see Mage_ImportExport_Model_Import_Entity_Product_Type_Abstract::prepareAttributesForSave()
	 */
	public function prepareAttributesForSave(array $rowData, $withDefaultValue = true)
	{
		$resultAttrs = array();
		foreach ($this->_getProductAttributes($rowData) as $attrCode => $attrParams) {
			if (!$attrParams['is_static']) {
				if (isset($rowData[$attrCode]) && strlen($rowData[$attrCode])) {
					$resultAttrs[$attrCode] =
					('select' == $attrParams['type'] || 'multiselect' == $attrParams['type'])
					? $attrParams['options'][strtolower($rowData[$attrCode])]
					: $rowData[$attrCode];
					//insert code to check url column
					if ("url_key"==$attrCode) {
						$resultAttrs[$attrCode] = $this->_checkUrlToChange($rowData[$attrCode],$rowData['sku']);
					}
				} elseif ($withDefaultValue && null !== $attrParams['default_value']) {
					$resultAttrs[$attrCode] = $attrParams['default_value'];
				}
			}
		}
		return $resultAttrs;
	}
	
	/**
	 * Customized method to check attribute URL_Key is duplicate and change it (import file product)
	 * 
	 * @param string $urlkey
	 * @param string $sku
	 * @return string >> unique url
	 */
	protected function _checkUrlToChange($urlkey, $sku)
	{
		//recovery attribute product data for search
		$_product = Mage::getModel('catalog/product');
		$_product->load( $_product->getIdBySku( $sku ) );
		$_product->setUrlKey( $urlkey );

		//load type of table to consult url_key
		$entity    = Mage::getModel('eav/entity')->setType( Mage_Catalog_Model_Product::ENTITY );
		$attribute = Mage::getModel('eav/entity_attribute')->loadByCode( Mage_Catalog_Model_Product::ENTITY,'url_key' );
		$attribute->setEntity( $entity );

		//verify if the url_key is unique (in file import and after database)
		if (in_array($urlkey, $this->_list_urlkey) || !$attribute->getEntity()->checkAttributeUniqueValue($attribute, $_product)) {
			$time = round(microtime(true) * 1000); //milliseconds to make url unique

			$urlkey = $urlkey.'-'.substr($time, 9, -4); //change the value because already exist
		}
		$this->_list_urlkey[] = $urlkey;
		
		return $urlkey;
	}
}
