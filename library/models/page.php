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
		
		echo e::$lhtml->file(EvolutionSite.'/configure/templates/'.$template.'.lhtml')->build();
		
	}
	
}