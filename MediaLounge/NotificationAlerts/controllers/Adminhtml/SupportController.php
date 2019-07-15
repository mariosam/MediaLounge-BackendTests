<?php
/**
 * NotificationAlerts Support page controller
 *
 * @category   MediaLounge
 * @package    MediaLounge_NotificationAlerts
 * @author     Mario SAM <eu@mariosam.com.br>
 */
class MediaLounge_NotificationAlerts_Adminhtml_SupportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Redirect to Media Lounge Support page
     *
     */
    public function indexAction()
    {
        //$url = Mage::getBaseUrl('web') . 'downloader/?return=' . urlencode(Mage::getUrl('adminhtml'));
        $this->getResponse()->setRedirect("http://www.medialounge.co.uk/magento-support-actual/");
    }
}
