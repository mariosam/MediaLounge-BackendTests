<?php
/**
 * Import entity product model OVERRIDE
 *
 * @category   MediaLounge
 * @package    MediaLounge_UniqueUrlProducts
 * @author     Mario SAM <eu@mariosam.com.br>
 */
class MediaLounge_UniqueUrlProducts_Model_Import_Entity_Product extends Mage_ImportExport_Model_Import_Entity_Product
{
	//save the all url in process to import (to check duplicate entries)
	protected $_list_urlkey = array();
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Common validation OVERRIDE
     *
     * @param array $rowData
     * @param int $rowNum
     * @param string|false|null $sku
	 */
	protected function _validate($rowData, $rowNum, $sku)
	{
		parent::_validate($rowData, $rowNum, $sku);
		
		//$this->_checkUrlKey($rowData, $rowNum, $sku);
		//Mage::log(var_export($this->_list_urlkey, TRUE));
	}
	
	//ESSE METODO SOH EXISTE NO 1.9 E 1.8, TEM Q FAZER DE OUTRO JEITO
	//DE REPENTE AFTER IMPORT OU BEFORE SAVE
	//MELHOR MESMO SERIA OVERRIDE A CLASS URL_KEY
	protected function _prepareAttributes($rowData, $rowScope, $attributes, $rowSku, $rowStore)
	{
		$product = Mage::getModel('importexport/import_proxy_product', $rowData);
		
		foreach ($rowData as $attrCode => $attrValue) {
			$attribute = $this->_getAttribute($attrCode);
			if ('multiselect' != $attribute->getFrontendInput()
					&& self::SCOPE_NULL == $rowScope
			) {
				continue; // skip attribute processing for SCOPE_NULL rows
			}
			$attrId = $attribute->getId();
			$backModel = $attribute->getBackendModel();
			$attrTable = $attribute->getBackend()->getTable();
			$storeIds = array(0);
		
			if ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
				$attrValue = gmstrftime($this->_getStrftimeFormat(), strtotime($attrValue));
			} elseif ('url_key' == $attribute->getAttributeCode()) {
				if (empty($attrValue)) {
					$attrValue = $product->formatUrlKey($product->getName());
				}
				//insert code: change the value if already exist
				$attrValue = $this->_checkUrlToChange($attrValue, $attribute, $product);
			} elseif ($backModel) {
				$attribute->getBackend()->beforeSave($product);
				$attrValue = $product->getData($attribute->getAttributeCode());
			}
			if (self::SCOPE_STORE == $rowScope) {
				if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
					// check website defaults already set
					if (!isset($attributes[$attrTable][$rowSku][$attrId][$rowStore])) {
						$storeIds = $this->_storeIdToWebsiteStoreIds[$rowStore];
					}
				} elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
					$storeIds = array($rowStore);
				}
			}
			foreach ($storeIds as $storeId) {
				if ('multiselect' == $attribute->getFrontendInput()) {
					if (!isset($attributes[$attrTable][$rowSku][$attrId][$storeId])) {
						$attributes[$attrTable][$rowSku][$attrId][$storeId] = '';
					} else {
						$attributes[$attrTable][$rowSku][$attrId][$storeId] .= ',';
					}
					$attributes[$attrTable][$rowSku][$attrId][$storeId] .= $attrValue;
				} else {
					$attributes[$attrTable][$rowSku][$attrId][$storeId] = $attrValue;
				}
			}
			$attribute->setBackendModel($backModel); // restore 'backend_model' to avoid 'default' setting
		}
		return $attributes;
	}
	
	/**
	 * Customized method to check attribute URL_Key is duplicate and change it (import file product)
	 * 
	 * @param Url_key  $attrValue
	 * @param Attribue $attribute
	 * @param Product  $product
	 * @return string  $attrValue >> with final value of url
	 */
	protected function _checkUrlToChange($attrValue, $attribute, $product)
	{
		//verify if the url_key is unique (in database and after in file import)
		if (!$attribute->getEntity()->checkAttributeUniqueValue($attribute, $product) 
				|| in_array($attrValue, $this->_list_urlkey)) {
			$time = round(microtime(true) * 1000); //milliseconds to make url unique

			$attrValue = $attrValue.'-'.substr($time, 9, -4); //change the value because already exist
		}
		$this->_list_urlkey[] = $attrValue;

		return $attrValue;
	}

	/**
	 * Customized method to validate attribute URL_Key (import file product)
	 *  
	 * @param array $rowData >> all data to import
	 * @param int $rowNum >> line number of file
	 * @param string $sku >> save the previos sky, line before
	 * @return boolean
	 */
	protected function _checkUrlKey($rowData, $rowNum, $sku)
	{
		//veriry if this main row with data
		$_sku = $rowData['sku'];
		if ($_sku == "" or $_sku == null) {
			return false;
		}

		//if url is blank is necessary obtain name to fill it
		$_urlkey = "";
		if (isset( $rowData['url_key'] )) {
			$_urlkey = $rowData['url_key'];
		}

		if (null==$_urlkey || ""==$_urlkey) {
			$_urlkey = str_replace(" ","-",trim(strtolower($rowData['name'])));
			$rowData['url_key'] = $_urlkey;
		}

		//prepare object product to consult database for unique URL key
		$product = Mage::getModel('catalog/product');
		$product->load( $product->getIdBySku( $_sku ) );
		$product->setUrlKey( $_urlkey );

		//load type of table to consult url_key
		$entity    = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY);
		$attribute = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY,'url_key');
		$attribute->setEntity( $entity );
		
		//verify if the url_key is unique
		if (!$entity->checkAttributeUniqueValue($attribute, $product)) {
			$time = round(microtime(true) * 1000);

			$_urlkey = $_urlkey.'-'.substr($time, 9, -4); //change the value because already exist
			$rowData['url_key'] = $_urlkey;
		}

		return true;
		//Mage::log(var_export($rowData, TRUE));
	}
	
}