<?php
/**
 * Controller for front page with feed lst
 *
 * @author Jordi Dosne @JiDaii
 */
class JDFeedSampleFeedModuleFrontController extends ModuleFrontController
{

	public function __construct()
	{
		parent::__construct();

		$this->context = Context::getContext();
	}
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();

		$this->context->smarty->assign(array(
			 'title' => Configuration::get('JDFEEDSAMPLE_TITLE', $this->context->cookie->id_lang),
			 'feed' => JDFeed::findAllActive($this->context->cookie->id_lang),
		));
		$this->setTemplate('feed.tpl');

	}
}
