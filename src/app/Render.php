<?php
/*
 * PoiXson phpUtils - PHP Utilities Library
 * @copyright 2004-2016
 * @license GPL-3
 * @author lorenzo at poixson.com
 * @link http://poixson.com/
 */
namespace pxn\phpUtils\app;


abstract class Render {

	protected $app = NULL;



	public function __construct(App $app) {
		$this->app = $app;
	}



	public abstract function getName();
	public abstract function getWeight();

	public abstract function doRender();



}
