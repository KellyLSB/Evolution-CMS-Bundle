<?php

namespace Bundles\CMS;
use Bundles\SQL\SQLBundle;
use Exception;
use e;

class Bundle extends SQLBundle {
	
	public function __construct($dir) {
		parent::__construct($dir);
	}
	
	public function _on_portal_route($path, $dir) {
		$this->route($path, $dir);
	}

	public function _on_portal_exception($path, $dir, $exception) {
		$this->route(array('special-exception'), array($dir));
	}
	
	public function page_array() {
		$pages = $this->getPages();
		
		$return = array();
		foreach($pages as $page) {
			$link = array('title' => $page->title, 'link'=>$page->url_on > 0 ? $page->url : '/'.$page->uri);
			
			$segs = explode('/', $page->uri);
			$cseg = count($segs);
			
			if(in_array('index', $segs)) {
				array_pop($segs);
				$cseg--;
			}
			
			$stack = array();
			foreach(array_reverse($segs) as $i=>$seg) {
				if($i < $cseg) $stack = array_merge(array('children' => $stack), $link);
				$stack = array($seg => $stack);
			}
			
			if($cseg < 1) $stack['index'] = $link;
			
			$return = e\array_merge_recursive_simple($stack, $return);
		}
		
		return array_reverse($return);
	}
	
	public function route($path, $dir, $index = false) {
		/**
		 * Set Index Page
		 */
		if(empty($path)) $path = array('index');
		
		$uri = strtolower(implode('/', $path));
		
		$array = e::$sql->select('cms.page', array('uri' => $uri))->row();
		
		if($array) {
			$this->getPage($array)->output();

			e\Complete();
		}
		
		else if($index !== true) {
			$path[] = 'index';
			$this->route($path, $dir, true);
		}
		
	}
	
}