<?php

namespace Merconis\CustomHoehenflug;

class mod_merconis_custom_dummy extends \Module {
	public function generate() {
		if (TL_MODE == 'BE') {
			$objTemplate = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### MERCONIS CUSTOM DUMMY MODULE ###';
			return $objTemplate->parse();
		}
		return parent::generate();
	}
	
	public function compile() {
        $this->strTemplate = 'mod_merconis_custom_dummy';
        $this->Template = new \FrontendTemplate($this->strTemplate);
	}
}