<?php

class Magneto_Debug_Block_Versions extends Magneto_Debug_Block_Abstract
{

    protected function getItems()
    {
        $modulesDefault = array(
            'Mage_Admin',
            'Mage_Adminhtml',
            'Mage_AdminNotification',
            'Mage_Backup',
            'Mage_Catalog',
            'Mage_CatalogIndex',
            'Mage_CatalogInventory',
            'Mage_CatalogRule',
            'Mage_CatalogSearch',
            'Mage_Checkout',
            'Mage_Cms',
            'Mage_Contacts',
            'Mage_Core',
            'Mage_Cron',
            'Mage_Customer',
            'Mage_Dataflow',
            'Mage_Directory',
            'Mage_Eav',
            'Mage_GiftMessage',
            'Mage_GoogleAnalytics',
            'Mage_GoogleCheckout',
            'Mage_Index',
            'Mage_Install',
            'Mage_Log',
            'Mage_Media',
            'Mage_Newsletter',
            'Mage_Page',
            'Mage_Paygate',
            'Mage_Payment',
            'Mage_Paypal',
            'Mage_PaypalUk',
            'Mage_Poll',
            'Mage_ProductAlert',
            'Mage_Rating',
            'Mage_Reports',
            'Mage_Review',
            'Mage_Rss',
            'Mage_Rule',
            'Mage_Sales',
            'Mage_SalesRule',
            'Mage_Sendfriend',
            'Mage_Shipping',
            'Mage_Sitemap',
            'Mage_Tag',
            'Mage_Tax',
            'Mage_Usa',
            'Mage_Wishlist',
            'Mage_Api',
            'Mage_Api2',
            'Mage_Authorizenet',
            'Mage_Bundle',
            'Mage_Captcha',
            'Mage_Centinel',
            'Mage_Compiler',
            'Mage_Connect',
            'Mage_CurrencySymbol',
            'Mage_Downloadable',
            'Mage_ImportExport',
            'Mage_Oauth',
            'Mage_PageCache',
            'Mage_Persistent',
            'Mage_Weee',
            'Mage_Widget',
            'Mage_XmlConnect',
        );

        $items = array();

        $modulesConfig = Mage::getConfig()->getModuleConfig();
        foreach ($modulesConfig as $node) {
            foreach ($node as $module => $data) {
                if (!in_array($module, $modulesDefault)) {
                    $items[] = array(
                        "module" => $module,
                        "codePool" => $data->codePool,
                        "active" => $data->active,
                        "version" => $data->version
                    );
                }
            }
        }

        return $items;
    }

}

