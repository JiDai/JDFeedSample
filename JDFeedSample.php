<?php
/**
 * JDFeedSample Module main file
 * It is a sample to demonstrate how to organize a Prestashop 1.5 module
 * It uses new developpement features of Prestashop 1.5.x version.
 *  - ModuleFrontController class
 *  - AdminController class for displaying tab with ObjectModel CRUD
 *  - New folder structure
 *  - etc...
 *
 * @author Jordi Dosne (@JiDaii)
 * @version 1.0
 */
class JDFeedSample extends Module
{
	/**
	 * Initialize the module
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->name = 'JDFeedSample';
		$this->tab = 'content_management';
		$this->version = '1.0';
		$this->author = 'Jordi Dosne';
		$this->module_key = '';

		$this->_config = Configuration::getMultiple(array(
			'JDFEEDSAMPLE_TITLE',
			'JDFEEDSAMPLE_ORDER',
			'JDFEEDSAMPLE_SHOW_COUNT',
		));

		parent::__construct();

		$this->displayName = $this->l('JDFeedSample');
		$this->description = $this->l('Module for easily creating feed.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}

	/**
	 * Install the module. Used by the Prestashop module installer
	 *
	 * @return boolean
	 * @access public
	 */
	public function install()
	{
		if( !parent::install()
			|| !$this->_createSQLTables()
			|| !$this->_createConfig()
			|| !$this->_installTab()
			|| !$this->registerHook('leftColumn')
		)
			return false;
		return true;
	}

	/**
	 * Uninstall the module. Used by the Prestashop module uninstaller
	 *
	 * @return boolean
	 * @access public
	 */
	public function uninstall()
	{
		if( !$this->_deleteTables() || !$this->_deleteConfig() || !$this->_deleteTab() || !parent::uninstall() )
			return false;

		return true;
	}

	/**
	 * Create tables
	 *
	 * @return boolean
	 */
	private function _createSQLTables()
	{
		$sql = "CREATE TABLE `" . _DB_PREFIX_ . "jd_feed` (
			`id_jd_feed` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`active` tinyint(4) unsigned NOT NULL DEFAULT 0,
			`date_add` datetime DEFAULT NULL,
			`date_upd` datetime DEFAULT NULL,
			PRIMARY KEY (`id_jd_feed`)
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if( !Db::getInstance()->execute($sql, false) )
			return $this->_abortInstall($this->l('Unable to create table `jd_feed`'));

		$sql = "CREATE TABLE `" . _DB_PREFIX_ . "jd_feed_lang` (
			`id_jd_feed` int(11) unsigned NOT NULL,
			`id_lang` int(11) unsigned NOT NULL,
			`title` VARCHAR(255) DEFAULT '',
			`description` VARCHAR(255) DEFAULT '',
			PRIMARY KEY (`id_jd_feed`,`id_lang`)
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		if( !Db::getInstance()->execute($sql, false) )
			return $this->_abortInstall($this->l('Unable to create table `jd_feed_lang`'));

		return true;
	}

	/**
	 * Delete tables
	 *
	 * @return boolean
	 */
	private function _deleteTables()
	{
		$sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "jd_feed`;";
		if( !Db::getInstance()->execute($sql) )
			return $this->_abortInstall($this->l('Unable to drop table `jd_feed`'));
		$sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "jd_feed_lang`;";
		if( !Db::getInstance()->execute($sql) )
			return $this->_abortInstall($this->l('Unable to drop table `jd_feed_lang`'));

		return true;
	}

	/**
	 * Initialize config values
	 *
	 * @return boolean
	 */
	private function _createConfig()
	{
		Configuration::updateValue('JDFEEDSAMPLE_ORDER', 2);
		Configuration::updateValue('JDFEEDSAMPLE_SHOW_COUNT', 0);
		$c = Configuration::getMultiple(array(
			'JDFEEDSAMPLE_TITLE',
			'JDFEEDSAMPLE_ORDER',
			'JDFEEDSAMPLE_SHOW_COUNT',
		 ));
		$this->_config = array_merge($this->_config, $c);
		return true;
	}

	/**
	 * Delete config values
	 *
	 * @return boolean
	 */
	private function _deleteConfig()
	{
		$configKeys = array_keys($this->_config);
		foreach( $configKeys as $key )
		{
			if( !Configuration::deleteByName($key) )
				return sprintf($this->l('Unable to delete config %s'), $key);
		}
		return true;
	}


	/**
	 * Install tab for subscriptions
	 *
	 * @return boolean
	 */
	private function _installTab()
	{
		/* If the "AdminSubscriptions" tab does not exist yet, create it */
		if( !$id_tab = Tab::getIdFromClassName('AdminFeed') )
		{
			$tab = new Tab();
			$tab->class_name = 'AdminFeed'; // Class of admin controller, without 'Controller'
			$tab->module = $this->name; // Link class to this module
			$tab->id_parent =  (int) Tab::getIdFromClassName('AdminParentModules');   // Set 0 if you want show Tab at first level
			/**
			 * Class list represent tab at first level
			 * - AdminCatalog
			 * - AdminParentOrders
			 * - AdminParentCustomer
			 * - AdminPriceRule
			 * - AdminParentShipping
			 * - AdminParentLocalization
			 * - AdminParentModules
			 * - AdminParentPreferences
			 * - AdminTools
			 * - AdminAdmin
			 * - AdminParentStats
			 * - AdminStock
			 */
			foreach( Language::getLanguages(false) as $lang )
				$tab->name[(int) $lang['id_lang']] = 'Feed';
			if( !$tab->save() )
				return $this->_abortInstall($this->l('Unable to create the "AdminFeed" tab'));
			// If you set Tab at first level, you must provide an icon
			if( !@copy(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.gif', _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 't' . DIRECTORY_SEPARATOR . 'AdminFeed.gif') )
				return $this->_abortInstall(sprintf($this->l('Unable to copy logo.gif in %s'), _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 't' . DIRECTORY_SEPARATOR));
		}
		else
			$tab = new Tab((int) $id_tab);
		return true;

	}

	/**
	 * Delete tab for subscriptions
	 *
	 * @return boolean
	 */
	private function _deleteTab()
	{
		if( $id_tab = Tab::getIdFromClassName('AdminFeed') )
		{
			$tab = new Tab((int) $id_tab);
			if( !$tab->delete() )
				$this->_abortInstall(sprintf($this->l('Unable to delete tab')));
		}
		return true;
	}

	/**
	 * Set installation errors and return false
	 *
	 * @param string $error Installation abortion reason
	 * @return boolean Always false
	 */
	protected function _abortInstall( $error )
	{
		if( version_compare(_PS_VERSION_, '1.5.0.0 ', '>=') )
			$this->_errors[] = $error;
		else
			echo '<div class="error">' . strip_tags($error) . '</div>';

		return false;
	}


	/**
	 * Get content of module configuration page
	 *
	 * @return string
	 */
	public function getContent()
	{
		$html = '';
		$id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
		$languages = Language::getLanguages(false);
		if( Tools::isSubmit('submitConfig') )
		{
			$message_trads = array();
			foreach ($_POST as $key => $value)
				if (preg_match('/JDFEEDSAMPLE_TITLE_/', $key))
				{
					$id_lang = str_replace('JDFEEDSAMPLE_TITLE_', '', $key);
					$message_trads[(int)$id_lang] = $value;
				}
			Configuration::updateValue('JDFEEDSAMPLE_TITLE', $message_trads);
			Configuration::updateValue('JDFEEDSAMPLE_ORDER', Tools::getValue('JDFEEDSAMPLE_ORDER'));
			Configuration::updateValue('JDFEEDSAMPLE_SHOW_COUNT', Tools::getValue('JDFEEDSAMPLE_SHOW_COUNT'));
			$html .= '<div class="conf confirm">' . $this->l('Configuration updated') . '</div>';
		}

		$html .= '
			<h2>' . $this->displayName . '</h2>
			<div class="clear">&nbsp;</div>
			<form action="' . htmlentities($_SERVER['REQUEST_URI']) . '" method="post">
				<fieldset>
					<label>' . $this->l('Title of feed page') . '</label>
					<div class="margin-form">
				';
			$values = Configuration::getInt('JDFEEDSAMPLE_TITLE');
			foreach ($languages as $language)
				$html .= '
						<div id="ccont_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $id_lang_default ? 'block' : 'none').';float: left;">
							<input type="text" name="JDFEEDSAMPLE_TITLE_'.$language['id_lang'].'" value="'.(isset($values[$language['id_lang']]) ? $values[$language['id_lang']] : '').'" />
						</div>
				';
				$html .= $this->displayFlags($languages, $id_lang_default, 'ccont', 'ccont', true).'
					</div>
					<div class="clear">&nbsp;</div>
					<label>' . $this->l('Feed list order ') . '</label>
					<div class="margin-form">
						<select name="JDFEEDSAMPLE_ORDER">
							<option value="1" ' . (Tools::getValue('JDFEEDSAMPLE_ORDER', $this->_config['JDFEEDSAMPLE_ORDER']) == 1 ? 'selected="selected"' : '') . '>' . $this->l('By add date desc') . '</option>
							<option value="2" ' . (Tools::getValue('JDFEEDSAMPLE_ORDER', $this->_config['JDFEEDSAMPLE_ORDER']) == 2 ? 'selected="selected"' : '') . '>' . $this->l('By title') . '</option>
						</select>
					</div>
					<div class="clear">&nbsp;</div>
					<label>' . $this->l('Number of feed shown on block :') . '</label>
					<div class="margin-form">
						<input type="radio" name="JDFEEDSAMPLE_SHOW_COUNT" value="0" ' . (Tools::getValue('JDFEEDSAMPLE_SHOW_COUNT', $this->_config['JDFEEDSAMPLE_SHOW_COUNT']) == 0 ? 'checked="checked"' : '') . '>&nbsp;&nbsp;' . $this->l('All') . '&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" name="JDFEEDSAMPLE_SHOW_COUNT" value="3" ' . (Tools::getValue('JDFEEDSAMPLE_SHOW_COUNT', $this->_config['JDFEEDSAMPLE_SHOW_COUNT']) == 3 ? 'checked="checked"' : '') . '>&nbsp;&nbsp;3 ' . $this->l('last items') . '&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" name="JDFEEDSAMPLE_SHOW_COUNT" value="5" ' . (Tools::getValue('JDFEEDSAMPLE_SHOW_COUNT', $this->_config['JDFEEDSAMPLE_SHOW_COUNT']) == 5 ? 'checked="checked"' : '') . '>&nbsp;&nbsp;5 ' . $this->l('last items') . '&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" name="JDFEEDSAMPLE_SHOW_COUNT" value="10" ' . (Tools::getValue('JDFEEDSAMPLE_SHOW_COUNT', $this->_config['JDFEEDSAMPLE_SHOW_COUNT']) == 10 ? 'checked="checked"' : '') . '>&nbsp;&nbsp;10 ' . $this->l('last items') . '
					</div>
					<div class="margin-form">
						<input class="button" name="submitConfig" value="' . $this->l('Update settings') . '" type="submit" />
					</div>
				</fieldset>
			</form>
		';
		return $html;
	}

	/**
	 * Hook for displaying block with last feed items
	 *
	 * @param type $params
	 * @return type
	 */
	public function hookLeftColumn($params)
	{
		require dirname(__FILE__).'/classes/JDFeed.php';

		$config = Configuration::getMultiple(array(
			 'JDFEEDSAMPLE_SHOW_COUNT',
			 'JDFEEDSAMPLE_ORDER',
		));
		$feed = JDFeed::findLastAdded($params['cookie']->id_lang, $config['JDFEEDSAMPLE_ORDER'], $config['JDFEEDSAMPLE_SHOW_COUNT']);
		$this->context->smarty->assign(array(
			 'title' => Configuration::get('JDFEEDSAMPLE_TITLE', $this->context->cookie->id_lang),
			 'feed' => $feed,
		));
 	 	return $this->display(__FILE__, 'blockfeed.tpl');
	}
}
