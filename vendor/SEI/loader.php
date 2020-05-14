<?php
namespace SEI;

/**
 * SEI Loader
 * @author arsenleontijevic
 *
 */
class SeiLoader {
	
	/**
	 * 
	 * @param stirng $name
	 * @throws \Exception
	 */
	static public function load($name) {
		$class = __DIR__ . DIRECTORY_SEPARATOR . '/../' . $name . '.php';
		$class = str_replace('\\', '/', $class);
		if (!file_exists($class))
		{
			throw new \Exception("Unable to load $class.");
		}
		require_once($class);
	}
}

spl_autoload_register(__NAMESPACE__ .'\SeiLoader::load'); // As of PHP 5.3.0
