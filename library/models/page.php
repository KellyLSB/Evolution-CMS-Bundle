<?php

namespace Bundles\CMS\Models;
use Bundles\SQL\Model;
use Exception;
use stack;
use e;

class Page extends Model {
	
	public function output() {
		
		$array = $this->__toArray();
		$array['content'] = e::markdown($array['content']);
		$array['sidebar'] = e::markdown($array['sidebar']);
		e::configure('lhtml')->activeAddKey('hook', ':page', $array);
		
		$template = $this->template;
		
		if(trim($template) == 'default' && strlen(trim($this->sidebar)) > 2)
			$template = 'default-sidebar';

		$locations = array_reverse(e::configure('cms')->activeGet('templates'));
		foreach($locations as $location) if(is_file($file = $location.'/'.$template.'.lhtml'))
			{ echo e::$lhtml->file($file)->parse()->build(); break; }

	}
	
}