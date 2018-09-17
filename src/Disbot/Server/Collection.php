<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/16/18
 * Time: 9:45 PM
 */

namespace Disbot\Server;

interface Collection {
	public function add($obj);
	public function remove($obj);
	public function get($obj);
	public function exists($obj);
}