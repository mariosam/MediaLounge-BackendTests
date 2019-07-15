<?php
/**
 * NotificationAlerts observer
 *
 * @category   MediaLounge
 * @package    MediaLounge_NotificationAlerts
 * @author     Mario SAM <eu@mariosam.com.br>
 */
class MediaLounge_NotificationAlerts_Model_Observer
{
    /**
     * Predispath admin action controller
     *
     * @param Varien_Event_Observer $observer
     */
    public function preDispatch(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {

            $feedModel = Mage::getModel('notificationalerts/feed');

            $feedModel->checkMLUpdate();
        }
    }
}
