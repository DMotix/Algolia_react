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
class Ads_algolia_frontActionsModuleFrontController extends ModuleFrontController
{
	/**
	 * @var int
	 */
	public $id_search_alert;

	public function init()
	{
		parent::init();

		require_once($this->module->getLocalPath().'ads_algolia_front.php');
		$this->id_search_alert = (int)Tools::getValue('id_search_alert');
	}

	public function postProcess()
	{
		if (Tools::getValue('process') == 'remove')
			$this->processRemove();
	}

	/**
	 * Remove a search alert
	 */
	public function processRemove()
	{
		// check if product exists
		$search_alert = new SearchAlert($this->id_search_alert);
		if (!Validate::isLoadedObject($search_alert))
			die('0');

		if (!$search_alert->delete())
			die('0');

		die('1');
	}
}