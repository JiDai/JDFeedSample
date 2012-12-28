<?php
/**
 * JDFeed object model
 *
 * @author Jordi Dosne @JiDaii
 */
class JDFeed extends ObjectModel
{

	public $id;

	/**
	 * Status for display. Si active = 1 on affiche sur le front
	 *
	 * @var int
	 */
	public $active = 0;

	/**
	 * Object date de la création (automatique par PS)
	 *
	 * @var string
	 */
	public $date_add;

	/**
	 * Object date de la dernière modification (automatique par PS)
	 *
	 * @var string
	 */
	public $date_upd;

	/**
	 * Titre traduite
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Description traduite
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		 'table' => 'jd_feed',
		 'primary' => 'id_jd_feed',
		 'multilang' => true,
		 'multilang_shop' => false,
		 'fields' => array(
			  'active' => array('type' => self::TYPE_BOOL),
			  'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			  'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			  // Translated fields
			  'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
			  'description' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
		 ),
	);

	/**
	 * Trouve tous les éléments du flux qui sont actif
	 *
	 * @param int $id_lang
	 * @return array
	 */
	public static function findAllActive( $id_lang )
	{
		$res = Db::getInstance()->executeS('
			SELECT *
			FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` a
			JOIN `' . _DB_PREFIX_ . self::$definition['table'] . '_lang` al
				ON a.`'.self::$definition['primary'].'` = al.`'.self::$definition['primary'].'`
					AND al.`id_lang` = '.(int)$id_lang.'
			WHERE a.`active` = 1
			ORDER BY al.`title` ASC
		');
		return ObjectModel::hydrateCollection('JDFeed', $res, $id_lang);
	}


	/**
	 * Trouve tous les éléments du flux qui sont actif ou non
	 *
	 * @param int $id_lang
	 * @return array
	 */
	public static function findAll( $id_lang )
	{
		$res = Db::getInstance()->executeS('
			SELECT *
			FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` a
			JOIN `' . _DB_PREFIX_ . self::$definition['table'] . '_lang` al
				ON a.`'.self::$definition['primary'].'` = al.`'.self::$definition['primary'].'`
					AND al.`id_lang` = '.(int)$id_lang.'
			ORDER BY al.`title` ASC
		');
		return ObjectModel::hydrateCollection('JDFeed', $res, $id_lang);
	}

	/**
	 * Trouve les dernier éléments du flux par date de création ou titre
	 *
	 * @param int $id_lang
	 * @param int $order
	 * @param int $count
	 * @return array
	 */
	public static function findLastAdded( $id_lang, $order = '', $count = 0 )
	{
		$sql = '
			SELECT *
			FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` a
			JOIN `' . _DB_PREFIX_ . self::$definition['table'] . '_lang` al
				ON a.`'.self::$definition['primary'].'` = al.`'.self::$definition['primary'].'`
					AND al.`id_lang` = '.(int)$id_lang.'
			WHERE a.`active` = 1
		';
		switch( $order )
		{
			case '1':
				$sql .= ' ORDER BY a.`date_add` DESC';
				break;
			case '2':
				$sql .= ' ORDER BY al.`title` ASC';
				break;
		}
		if( $count > 0 )
		{
			$sql .= ' LIMIT '.(int)$count;
		}
		$res = Db::getInstance()->executeS($sql);
		return ObjectModel::hydrateCollection('JDFeed', $res, $id_lang);
	}

}
