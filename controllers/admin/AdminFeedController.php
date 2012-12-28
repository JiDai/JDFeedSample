<?php

/**
 * AdminFeedController classe
 * Définit l'onglet de l'administration gérer le CRUD de l'ObjectModel
 *
 * @author Jordi Dosne @JiDaii
 */
include_once(dirname(__FILE__) . '/../../classes/JDFeed.php');

class AdminFeedController extends ModuleAdminController
{

	public function __construct()
	{
		// Table Mysql
		$this->table = 'jd_feed';
		$this->identifier = 'id_jd_feed';
		// Définition de la classe ObjectModel lié au controller
		$this->className = 'JDFeed';
		
		// L'ObjectModel est-il traduisible, oui
		$this->lang = true;

		// Action prédéfinies à afficher en bout de ligne du tableau
		$this->addRowAction('edit');
		$this->addRowAction('delete');

		// Champs visible dans le tableau
		$this->fields_list = array(
			'id_jd_feed' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25,
			),
			'title' => array(
				'title' => $this->l('Title'),
				 'width' => 200,
			),
			'description' => array(
				'title' => $this->l('Description'),
				'callback' => 'descriptionFieldCallback', // Défini un callback pour change la valeur de la cellule juste avant de l'afficher
			),
			'active' => array(
				'title' => $this->l('Displayed'),
				'width' => 25,
				'active' => 'status', // Utilise un bouton (croix rouge et check vert) pour afficher le statut
				'align' => 'center',
				'type' => 'bool',
				'orderby' => false,
			),
			'date_add' => array(
				'title' => $this->l('Added'),
				'width' => 50,
				'type' => 'date',
			),
			'date_upd' => array(
				'title' => $this->l('Updated'),
				'width' => 50,
				'type' => 'date',
			),
		);


		parent::__construct();
	}

	public function descriptionFieldCallback( $value, $model )
	{
		return nl2br($value);
	}

	public function renderForm()
	{
		if (!$this->loadObject(true))
			return;
		
		// Définition des champs du formulaire permettant la génération des for d'édition et de création
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Feed'),
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Title:'),
					'name' => 'title',
					'required' => true,
					'lang' => true,
					'class' => 'copy2friendlyUrl',
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'textarea',
					'label' => $this->l('Description:'),
					'name' => 'description',
					'required' => true,
					'lang' => true,
					'rows' => 5,
					'cols' => 40,
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);

		return parent::renderForm();
	}

}
