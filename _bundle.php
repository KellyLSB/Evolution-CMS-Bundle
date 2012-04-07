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

	public function page_structure($depth = 2, $html = false) {
		if(is_string($depth)) $depth = (int) $depth;
		$pages = $this->getPages();

		/**
		 * The current URL
		 */
		$curr_url = $_SERVER['REDIRECT_URL'];
		$curr_url_index = $curr_url === '/' ? '/index' : $curr_url.'/index';

		/**
		 * Get our array structure of pages
		 */
		$return = array();
		foreach($pages->order('`uri`', 'ASC') as $page) {

			/**
			 * Dont show the page in the NAV
			 */
			if($page->hidden > 0) continue;

			/**
			 * Explode the URI to get the segments
			 */
			$uri = !empty($page->uri) ? explode('/', $page->uri) : array(strtolower($page->title));

			/**
			 * Tell the array when it can build the page
			 */
			array_push($uri, '@---end');

			/**
			 * Process the placement in the array
			 */
			$arrayPiece = array(); $counter = 0;
			foreach(array_reverse($uri) as $seg) {
				if($counter === $depth) {
					$arrayPiece = array();
					break;
				}

				if($seg !== '@---end' && $seg !== 'index') {
					$counter++;
					if(isset($arrayPiece['selected']) && $arrayPiece['selected'])
						$arrayPiece = array(
							'children' => array($seg => $arrayPiece),
							'selected' => true
						);
					else $arrayPiece = array(
							'children' => array($seg => $arrayPiece),
							'selected' => false
						);
					continue;
				}

				if('/'.$page->uri === $curr_url)
					$selected = true;
				else if('/'.$page->uri === $curr_url_index)
					$selected = true;
				else
					$selected = false;

				$arrayPiece = array(
					'title' => $page->title,
					'url' => $page->url_on ? $page->url : '/'.$page->uri,
					'priority' => $page->priority,
					'selected' => $selected
				);
			}

			/**
			 * Handle the top level index
			 */
			if($counter == 0) $arrayPiece = array('children' => array($seg => $arrayPiece));

			$return = e\array_merge_recursive_simple($return, $arrayPiece);
		}

		/**
		 * By default the index is merged into the children so lets shift the array
		 */
		$return = array_shift($return);

		/**
		 * Prioritize
		 */
		$prioritize = function($array) use (&$prioritize) {
			uasort($array, function($a, $b) {
				if ($a['priority'] == $b['priority'])
					return 0;
				return ($a['priority'] < $b['priority']) ? -1 : 1;
			});

			foreach($array as &$segment) {
				$segment['children'] = $prioritize($segment['children']); 
			}

			return $array;
		};

		$return = $prioritize($return);

		if(!$html) return $return;

		/**
		 * Render array into html
		 */
		$render = function($array) use (&$render) {
			static $depth = 0;
			$depth++;

			$html = "<ul class=\"depth-$depth\">";

			foreach($array as $key => $segment) {
				$html .= "<li>";

				if(isset($segment['title']) && isset($segment['url']))
					$html .= "<a class=\"".($segment['selected'] ? 'selected' : '')."\" href=\"$segment[url]\">$segment[title]</a>";
				else
					$html .= "<a class=\"".($segment['selected'] ? 'selected' : '')."\">".ucwords($key)."</a>";

				if(isset($segment['children']))
					$html .= $render($segment['children']);
			}

			$html .= "</ul>";

			$depth--;
			return $html;
		};

		return $render($return);
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