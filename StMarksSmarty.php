<?php

final class StMarksSmarty extends Smarty {

	private static $singleton = null;

	const APP_KEY = 'app';
	const ENGINE_KEY = 'engine';

	private $messages = array();
	private $stylesheets = array();
	
	private static function testWriteableDirectory($directory) {
		$success = false;
		if (file_exists($directory)) {
			if (is_dir($directory)) {
				if (is_writable($directory)) {
					$success = true;
				} else {
					$success = chmod($directory, 0775);
				}
			}
		} elseif (!file_exists($directory)) {
			$success = mkdir($directory);
		}
		
		if (!$success) {
			throw new StMarksSmarty_Exception(
				"The directory '{$directory}' cannot be created or cannot be made writeable",
				StMarksSmarty_Exception::UNWRITABLE_DIRECTORY
			);
		}
	}
	
	private static function testReadableDirectory($directory) {
		$success = false;
		
		if (file_exists($directory)) {
			if (is_dir($directory)) {
				if (is_readable($directory)) {
					$success = true;
				} else {
					$success = chmod($directory, 0555);
				}
			}
		} else {
			$success = mkdir($directory);
			throw new StMarksSmarty_Exception(
				"The directory '{$directory}' was created (should have already existed and been populated)",
				StMarksSmarty_Exception::MISSING_FILES
			);
		}
		
		if (!$success) {
			throw new StMarksSmarty_Exception(
				"The directory '{$directory}' is not readable",
				StMarksSmarty_Exception::UNREADABLE_DIRECTORY
			);
		}
	}
	
	private static function directoryArrayMerge($appDir, $engineDir, $arrayResult = true) {
		if ($arrayResult) {
			if (!empty($appDir)) {
				if (is_array($appDir)) {
					return array_merge($appDir, $engineDir);
				} else {
					return array_merge(array(self::APP_KEY => $appDir), $engineDir);
				}
			} else {
				return $engineDir;
			}
		} else {
			if (!empty($appDir)) {
				return $appDir;
			} else {
				return $engineDir;
			}
		}
	}
	
	/**
	 * singleton
	 *
	 * @param string|string[] $template (Optional)
	 * @param string|string[] $config (Optional)
	 * @param string $compile (Optional)
	 * @param string $cache (Optional)
	 *
	 * @return StMarksSmarty
	 **/
	public static function getSmarty($template = null, $config = null, $compile = null, $cache = null) {
		if (self::$singleton === null) {
			self::$singleton = new self($template, $config, $compile, $cache);
		}
		return self::$singleton;
	}
	
	/**
	 * singleton
	 *
	 * @param string|string[] $template (Optional)
	 * @param string|string[] $config (Optional)
	 * @param string $compile (Optional)
	 * @param string $cache (Optional)
	 *
	 * @return void
	 **/
	public function __construct($template = null, $config = null, $compile = null, $cache = null) {
		if (self::$singleton !== null) {
			throw new StMarksSmarty_Exception(
				'StMarksSmarty is a singleton class, use the factory method StMarksSmarty::getSmarty() instead of ' . __METHOD__,
				StMarksSmarty_Exception::SINGLETON
			);
		}
		parent::__construct();
		self::$singleton = $this;
		
		$engineTemplateDir = array(self::ENGINE_KEY => __DIR__ . '/templates');
		$engineConfigDir = array(self::ENGINE_KEY => __DIR__ . '/configs');
		$engineCompileDir = __DIR__ . '/templates_c';
		$engineCacheDir = __DIR__ . '/cache';
		
		$this->setTemplateDir(self::directoryArrayMerge($template, $engineTemplateDir));
		$this->setConfigDir(self::directoryArrayMerge($config, $engineConfigDir));
		$this->setCompileDir(self::directoryArrayMerge($compile, $engineCompileDir, false));
		$this->setCacheDir(self::directoryArrayMerge($cache, $engineCacheDir, false));
		
		foreach($this->getTemplateDir() as $key => $dir) {
			self::testReadableDirectory($dir);
		}
		
		foreach($this->getConfigDir() as $key => $dir) {
			self::testReadableDirectory($dir);
		}
		
		self::testWriteableDirectory($this->getCompileDir());
		self::testWriteableDirectory($this->getCacheDir());
				
		// FIXME ...wow. Just... wow.
		$preliminaryMetadata = array(
			'APP_URL' => (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on' ? 'http://' : 'https://') . $_SERVER['SERVER_NAME'] . preg_replace("|^{$_SERVER['DOCUMENT_ROOT']}(.*)$|", '$1', str_replace('/vendor/smtech/stmarkssmarty', '', __DIR__)),
			'APP_NAME' => 'St. Mark&rsquo;s School'
		);
		$this->assign('metadata', $preliminaryMetadata);
		
		$this->stylesheets[] = $preliminaryMetadata['APP_URL'] . '/vendor/smtech/stmarkssmarty/stylesheets/stylesheet.css';
		$this->assign('stylesheets', $this->stylesheets);
	}
	
	/** singleton */
	private function __clone() {
		if (self::$singleton !== null) {
			throw new StMarksSmarty_Exception(
				'StMarksSmarty is a singleton class, use the factory method StMarksSmarty::getSmarty() instead of ' . __METHOD__,
				StMarksSmarty_Exception::SINGLETON
			);
		}
		parent::__clone();
	}
	
	/** singleton */
	private function __wakeup() {
		if (self::$singleton !== null) {
			throw new StMarksSmarty_Exception(
				'StMarksSmarty is a singleton class, use the factory method StMarksSmarty::getSmarty() instead of ' . __METHOD__,
				StMarksSmarty_Exception::SINGLETON
			);
		}
		parent::__wakeup();
	}
	
	public function addMessage($title, $content, $class = 'message') {
		$this->messages[] = new NotificationMessage($title, $content, $class);
	}
	
	public function display($template = 'page.tpl', $cache_id = null, $compile_id = null, $parent = null) {
		$this->assign('messages', $this->messages);
		parent::display($template, $cache_id, $compile_id, $parent);
	}
}

class StMarksSmarty_Exception extends Exception {
	const SINGLETON = 1;
	const UNREADABLE_DIRECTORY = 2;
	const UNWRITABLE_DIRECTORY = 3;
	const MISSING_FILES = 4;
}
	
?>