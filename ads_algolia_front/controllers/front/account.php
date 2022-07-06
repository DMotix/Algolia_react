<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * @since 1.5.0
 */
class ads_algolia_frontAccountModuleFrontController extends ModuleFrontController
{
	public function init()
	{
        $this->display_column_left = false;
		parent::init();
		require_once($this->module->getLocalPath().'classes/SearchAlert.php');
	}

	public function initContent()
	{
		parent::initContent();

		if (!Context::getContext()->customer->isLogged())
			Tools::redirect('index.php?controller=authentication&redirect=module&module=ads_algolia_front&action=account');

		if (Context::getContext()->customer->id)
		{
			$this->context->smarty->assign('id_customer', Context::getContext()->customer->id);
			$this->context->smarty->assign(
				'searchAlerts',
                SearchAlert::getSearchAlerts((int)Context::getContext()->customer->id, (int)Context::getContext()->language->id)
			);

			$this->setTemplate('searchalert-account.tpl');
		}
	}

    public function setMedia()
    {
        parent::setMedia();

        $this->context->controller->addCSS($this->module->getPath() . 'views/css/search_alert.css');
        $this->addJqueryPlugin('fancybox');
    }
}