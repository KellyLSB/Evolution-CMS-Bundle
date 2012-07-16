<?php

namespace Bundles\CMS\Models;
use Bundles\SQL\Model;
use Exception;
use stack;
use e;

class Page extends Model {
	
	public function output() {
		
		/**
		 * Get content for the page and source it
		 */
		$array = $this->__toArray();
		$array['content'] = e::markdown($array['content']);
		$array['sidebar'] = e::markdown($array['sidebar']);
		e::configure('lhtml')->activeAddKey('hook', ':page', $array);
		
		// Get the template source code fully rendered
		$ret = e::$cms->returnTemplateCode($this->template, $array);

		// Output page to browser
		echo isset($ret) ? $ret : 'No template found for '.$template;
	}
	
}