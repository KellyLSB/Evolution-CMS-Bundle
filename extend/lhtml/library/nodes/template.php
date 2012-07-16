<?php

namespace Bundles\LHTML\Nodes;
use Bundles\LHTML\Node;
use Exception;
use e;

/**
 * Template Class - (Reverse Include)
 *
 * @package default
 * @author Kelly Lauren Summer Becker
 */
class Template extends Node {

	private $template;

	public function ready() {
		$this->template = empty($this->attributes['template']) ? 'pages/default' : $this->attributes['template'];
		unset($this->attributes['template']);
		$this->element = false;
	}
	
	public function build() {
		$found = false;
		$hook = $this->attributes;
		foreach($this->children as $child) {
			if(!is_object($child))
				continue;			
			if(!($child instanceof Node && strpos($child->fake_element, 'template:') === 0))
				continue;
				
			$name = str_replace('template:', '', $child->fake_element);
			$child->element = false;
			
			$hook = e\array_merge_recursive_simple($hook, array(
				$name => $child->build()
			));
			
			$found = true;
		}
		
		if(!$found) {
			$hook = e\array_merge_recursive_simple($hook, array(
				'content' => parent::build()
			));
		}

		e::configure('lhtml')->activeAddKey('hook', ':page', $hook);

		// Get the template source code fully rendered
		$ret = e::$cms->returnTemplateCode($this->template, $hook);
		
		// Output page to browser
		return isset($ret) ? $ret : 'No template found for '.$template;
	}
	
}