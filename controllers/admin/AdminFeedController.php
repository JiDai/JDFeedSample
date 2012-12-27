<?php

/**
 * AdminFeedController file
 * Class to handle feed item CRUD in prestashop administration
 *
 * @author Jordi Dosne @JiDaii
 */
include_once(dirname(__FILE__) . '/../../classes/JDFeed.php');

class AdminFeedController extends ModuleAdminController
{

	public function __construct()
	{
		$this->table = 'jd_feed';
		$this->identifier = 'id_jd_feed';
		$this->className = 'JDFeed';
		$this->lang = true;

		$this->addRowAction('edit');
		$this->addRowAction('delete');

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
				'callback' => 'descriptionFieldCallback', // Set a callback to change field value before display
			),
			'active' => array(
				'title' => $this->l('Displayed'),
				'width' => 25,
				'active' => 'status', // Use image button (cross and check) to handle displayed status
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
