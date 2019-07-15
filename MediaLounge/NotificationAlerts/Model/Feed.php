<?php
/**
 * NotificationAlerts feed model
 *
 * @category   MediaLounge
 * @package    MediaLounge_NotificationAlerts
 * @author     Mario SAM <eu@mariosam.com.br>
 */
class MediaLounge_NotificationAlerts_Model_Feed extends Mage_AdminNotification_Model_Feed
{
	const XML_MEDIALOUNGE_FEED_URL_PATH = 'system/notificationalerts/feed_url';

	/**
	 * Feed url
	 *
	 * @var string
	 */
	protected $_feedUrl;

	/**
	 * 
	 * @see Mage_AdminNotification_Model_Feed::_construct()
	 */
	protected function _construct()
	{
		parent::_construct();
	}
	
	/**
	 * Check feed for modification
	 *
	 * @return Mage_AdminNotification_Model_Feed
	 */
	public function checkMLUpdate()
	{
		if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
			//return $this;
		}
	
		$feedData = array();
	
		$feedXml = $this->getMLFeedData();
	
		if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
			foreach ($feedXml->channel->item as $item) {
				$feedData[] = array(
						'severity'      => (int)$item->severity,
						'date_added'    => $this->getDate((string)$item->pubDate),
						'title'         => (string)$item->title,
						'description'   => (string)$item->description,
						'url'           => (string)$item->link,
				);
			}
	
			if ($feedData) {
				Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));
			}
	
		}
		$this->setLastUpdate();
	
		return $this;
	}
	
	/**
	 * Retrieve feed url
	 *
	 * @return string
	 */
	public function getMLFeedUrl()
	{
		if (is_null($this->_feedUrl)) {
			$this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
			. Mage::getStoreConfig(self::XML_MEDIALOUNGE_FEED_URL_PATH);
		}
		return $this->_feedUrl;
	}
	
	/**
	 * Retrieve feed data as XML element
	 *
	 * @return SimpleXMLElement
	 */
	public function getMLFeedData()
	{
		$curl = new Varien_Http_Adapter_Curl();
		$curl->setConfig(array(
				'timeout'   => 2
		));
		$curl->write(Zend_Http_Client::GET, $this->getMLFeedUrl(), '1.0');
		$data = $curl->read();
		if ($data === false) {
			return false;
		}
		$data = preg_split('/^\r?$/m', $data, 2);
		$data = trim($data[1]);
		$curl->close();
	
		try {
			$xml  = new SimpleXMLElement($data);
		}
		catch (Exception $e) {
			return false;
		}
	
		return $xml;
	}
	
	/**
	 * dont used XML, only RSS
	 * 
	 * @return SimpleXMLElement
	 */
	public function getMLFeedXml()
	{
		//Mage::log('MediaLounge_NotificationAlerts_Model_Feed >> getMLFeedXml()');
		try {
			$data = $this->getMLFeedData();
			$xml  = new SimpleXMLElement($data);
		}
		catch (Exception $e) {
			$xml  = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?>');
		}
	
		return $xml;
	}
}