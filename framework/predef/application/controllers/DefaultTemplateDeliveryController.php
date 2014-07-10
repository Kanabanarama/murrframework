<?php

/**
 * DefaultTemplateDeliveryController
 * Murrmurr framework
 *
 * If no class could be routed, this class is the fallback to display a html page with the routed name
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @version 3.1.4
 */

class DefaultTemplateDeliveryController extends BaseController
{
	private $strTemplate;

	/*
	 * @param string $strSubject  Subject that is to be handled by the Controller
	 * @param string $strAction  Other parameters for the Controller
	 */
	public function __construct($strSubject = null, $strAction = null, $aParams = null, $strTemplateName) {
		$this->strTemplate = $strTemplateName.'.htm';
		parent::__construct();
	}

	public function process() {
		//echo('bypass '.$this->strTemplate);
		$htmlpage = new TemplateView($this->strTemplate);
		if($htmlpage->templateFileExists()) {
			$htmlpage->publish($this);
		} else {
			throw new Exception('There was no dedicated controller and no bypass template found!<br />(The path for the bypass template is "'.TEMPLATE_DIR.$this->strTemplate.'")', 3);
		}
	}
}

?>