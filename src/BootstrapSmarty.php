<?php

/** BootstrapSmarty and related classes */

namespace Battis\BootstrapSmarty;

use Battis\DataUtilities;

/**
 * A wrapper for Smarty to set (and maintain) defaults within a Bootstrap
 * UI environment
 *
 * @author Seth Battis <seth@battis.net>
 **/
class BootstrapSmarty extends \Smarty {

	/**
	 * @var BootstrapSmarty|NULL Reference to the singleton BootstrapSmarty
	 *		instance
	 **/
	protected static $singleton = null;


	/**
	 * Default key for app-specified entry in lists of template and config
	 * directories
	 **/
	const APP_KEY = 'app';

	/**
	 * Default key for BootstrapSmarty-specified entry in lists of template and
	 * config directories
	 **/
	const UI_KEY = 'BootstrapSmarty';
	
	
	/** Module name for eternicode/bootstrap-datepicker */
	const MODULE_DATEPICKER = 'eternicode/bootstrap-datepicker';


	/**
	 * @var string[] Directory used by BootstrapSmarty for base
	 *		templates (always included in template directories list)
	 **/
	private $uiTemplateDir = null;
	
	/**
	 * @var string[] Directory used by BootstrapSmarty for base configs
	 *		(always included in config directories list)
	 **/
	private $uiConfigDir = null;
	
	/**
	 * @var string Default directory used by BootstrapSmarty for
	 *		compiled templates (can be overridden)
	 **/
	private $uiCompileDir = null;
	
	/**
	 * @var string Default directory used by BootstrapSmarty for cache
	 *		files (can be overriden)
	 **/
	private $uiCacheDir = null;

	/**
	 * @var NotificationMessage[] List of pending notification messages
	 *		to be displayed
	 **/
	private $messages = array();
	
	/** @var string[] $stylesheets List of stylesheets to be applied */
	private $stylesheets = array();
		
	/**
	 * Test a file systems directory for writeability by the Apache user
	 *
	 * Note that this method throws an exception _rather than_ returning false, as
	 * no pages can be displayed using the Smarty templating system if the
	 * directories being checked do not exist. An application that was fault-
	 * tolerant enough to work around these missing directories should catch this
	 * exception, rather than expecting a false result.
	 *
	 * @param string $directory
	 *
	 * @return boolean TRUE if the directory is writeable
	 *
	 * @throws BootstrapSmarty_Exception UNWRITABLE_DIRECTORY If the directory is not
	 *		writeable
	 **/
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
			throw new BootstrapSmarty_Exception(
				"The directory '{$directory}' cannot be created or cannot be made writeable",
				BootstrapSmarty_Exception::UNWRITABLE_DIRECTORY
			);
		}
	}
	
	/**
	 * Test a file system directory for readability by the Apache user
	 *
	 * Note that this method throws an exception _rather than_ returning false, as
	 * no pages can be displayed using the Smarty templating system if the
	 * directories being checked do not exist. An application that was fault-
	 * tolerant enough to work around these missing directories should catch this
	 * exception, rather than expecting a false result.
	 *
	 * @param string $directory
	 *
	 * @return boolean TRUE if the directory is writeable
	 *
	 * @throws BootstrapSmarty_Exception MISSING_FILES After creating the directory
	 *		(if the directory does not already exist)
	 * @throws BootstrapSmarty_Exception UNREADABLE_DIRECTORY If the directory
	 *		exists, but is not readable
	 **/
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
			/* TODO is this reasonable behavior, or should it simply treat the directory
			   as unreadable? */
			$success = mkdir($directory);
			throw new BootstrapSmarty_Exception(
				"The directory '{$directory}' was created (should have already existed and been populated)",
				BootstrapSmarty_Exception::MISSING_FILES
			);
		}
		
		if (!$success) {
			throw new BootstrapSmarty_Exception(
				"The directory '{$directory}' is not readable",
				BootstrapSmarty_Exception::UNREADABLE_DIRECTORY
			);
		}
	}
	
	/**
	 * Build an array of directories appending the BootstrapSmarty defaults
	 *
	 * @param string|string[] $appDir Application directory (or directories,
	 *		optionally with associative array keys for identification)
	 * @param string|string{} $uiDir BootstrapSmarty directory defaults
	 * @param boolean $arrayResult (Optional) Whether or not the result should be
	 *		a string or an array of strings (defaults to true, an array of strings)
	 **/
	private static function appendUiDefaults($appDir, $uiDir, $arrayResult = true) {
		
		/* FIXME Currently assumes that $uiDir will always be passed correctly as
		   either a string or an array of strings, but does no checks */
		
		if ($arrayResult) {
			if (!empty($appDir)) {
				if (is_array($appDir)) {
					return array_merge($appDir, $uiDir);
				} else {
					return array_merge(array(self::APP_KEY => $appDir), $uiDir);
				}
			} else {
				return $uiDir;
			}
		} else {
			if (!empty($appDir)) {
				return $appDir;
			} else {
				return $uiDir;
			}
		}
	}
	
	/**
	 * Return the singleton instance of BootstrapSmarty
	 *
	 * @param string|string[] $template (Optional) Additional Smarty template
	 *		directories
	 * @param string|string[] $config (Optional) Additional Smarty config
	 *		directories
	 * @param string $compile (Optional) Alternative Smarty compiled template
	 *		directory
	 * @param string $cache (Optional) Alternative Smarty cache directory
	 *
	 * @return BootstrapSmarty
	 *
	 * @see http://www.phptherightway.com/pages/Design-Patterns.html#singleton Singleton Design Pattern
	 **/
	public static function getSmarty($template = null, $config = null, $compile = null, $cache = null) {
		if (self::$singleton === null) {
			self::$singleton = new self($template, $config, $compile, $cache);
		}
		return self::$singleton;
	}
	
	/**
	 * Construct the singleton instance of BootstrapSmarty
	 *
	 * @deprecated Use singleton pattern BootstrapSmarty::getSmarty()
	 *
	 * @param string|string[] $template (Optional) Additional Smarty template
	 *		directories
	 * @param string|string[] $config (Optional) Additional Smarty config
	 *		directories
	 * @param string $compile (Optional) Alternative Smarty compiled template
	 *		directory
	 * @param string $cache (Optional) Alternative Smarty cache directory
	 *
	 * @return void
	 *
	 * @throws BootstrapSmarty_Exception SINGLETON If an instance of BootstrapSmarty already exists
	 *
	 * @see BootstrapSmarty::getSmarty() BootstrapSmarty::getSmarty()
	 * @see http://www.phptherightway.com/pages/Design-Patterns.html#singleton Singleton Design Pattern
	 **/
	public function __construct($template = null, $config = null, $compile = null, $cache = null) {
		if (self::$singleton !== null) {
			throw new BootstrapSmarty_Exception(
				'BootstrapSmarty is a singleton class, use the factory method BootstrapSmarty::getSmarty() instead of ' . __METHOD__,
				BootstrapSmarty_Exception::SINGLETON
			);
		} else {
			parent::__construct();
			self::$singleton = $this;
		}
		
		/* Default to local directories for use by Smarty */
		$this->uiTemplateDir = array(self::UI_KEY => realpath(__DIR__ . '/../templates'));
		$this->uiConfigDir = array(self::UI_KEY => realpath(__DIR__ . '/../configs'));
		$this->uiCompileDir = realpath(__DIR__ . '/../templates_c');
		$this->uiCacheDir = realpath(__DIR__ . '/../cache');
		
		/* Apply user additions and alternates */
		$this->setTemplateDir($template);
		$this->setConfigDir($config);
		$this->setCompileDir($compile);
		$this->setCacheDir($cache);
		
		/* Test all directories for use by Smarty */
		foreach($this->getTemplateDir() as $key => $dir) {
			self::testReadableDirectory($dir);
		}
		foreach($this->getConfigDir() as $key => $dir) {
			self::testReadableDirectory($dir);
		}
		self::testWriteableDirectory($this->getCompileDir());
		self::testWriteableDirectory($this->getCacheDir());
		
		/* Define base stylesheet */
		$this->stylesheets[self::UI_KEY] = (
				!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on' ?
					'http://' :
					'https://'
			) .
			$_SERVER['SERVER_NAME'] . preg_replace("|^{$_SERVER['DOCUMENT_ROOT']}(.*)/src$|", '$1', __DIR__) . '/css/BootstrapSmarty.css';
		
		/* set some reasonable defaults */
		$this->assign('BOOTSTRAPSMARTY_URL', (
				!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on' ?
					'http://' :
					'https://'
			) .
			$_SERVER['SERVER_NAME'] . preg_replace("|^{$_SERVER['DOCUMENT_ROOT']}(.*)/src$|", '$1', __DIR__));
		$this->assign('name', DataUtilities::titleCase(preg_replace('/[\-_]+/', ' ', basename($_SERVER['REQUEST_URI'], '.php'))));
		$this->assign('category', DataUtilities::titleCase(preg_replace('/[\-_]+/', ' ', basename(dirname($_SERVER['REQUEST_URI'])))));
		$this->assign('navbarActive', false);
	}
	
	/**
	 * Change any necessary properties after a shallow copy cloning
	 *
	 * @deprecated Use singleton pattern BootstrapSmarty::getSmarty()
	 *
	 * @throws BootstrapSmarty_Exception SINGLETON If method is invoked.
	 *
	 * @see BootstrapSmarty::getSmarty() BootstrapSmarty::getSmarty()
	 * @see http://php.net/manual/en/language.oop5.cloning.php#object.clone Object Cloning
	 * @see http://www.phptherightway.com/pages/Design-Patterns.html#singleton Singleton Design Pattern
	 **/
	private function __clone() {
		throw new BootstrapSmarty_Exception(
			'BootstrapSmarty is a singleton class, use the factory method BootstrapSmarty::getSmarty() instead of ' . __METHOD__,
			BootstrapSmarty_Exception::SINGLETON
		);
	}
	
	/**
	 * Reconstruct any resources used by an object upon unserialize()
	 *
	 * @deprecated Use singleton pattern BootstrapSmarty::getSmarty()
	 *
	 * @throws BootstrapSmarty_Exception SINGLETON If method is invoked.
	 *
	 * @see BootstrapSmarty::getSmarty() BootstrapSmarty::getSmarty()
	 * @see http://php.net/manual/en/oop4.magic-functions.php The magic functions *__sleep* and *__wakeup*
	 * @see http://www.phptherightway.com/pages/Design-Patterns.html#singleton Singleton Design Pattern
	 **/
	private function __wakeup() {
		throw new BootstrapSmarty_Exception(
			'BootstrapSmarty is a singleton class, use the factory method BootstrapSmarty::getSmarty() instead of ' . __METHOD__,
			BootstrapSmarty_Exception::SINGLETON
		);
	}
	
	/**
	 * Set the directories where templates are stored
	 *
	 * Preserves default BootstrapSmarty template directory to allow for extensions
	 * and applications of the base templates by application templates.
	 *
	 * @param string|string[] $template Additional Smarty template directories
	 * @param boolean $isConfig (Optional) Defaults to FALSE, set to TRUE when
	 *		Smarty::setTemplateDir() is aliased by Smarty::setConfigDir()
	 *
	 * @used-by BootstrapSmarty::setConfigDir()
	 *
	 * @see http://www.smarty.net/docs/en/api.set.template.dir.tpl Smarty::setTemplateDir()
	 **/
	public function setTemplateDir($template, $isConfig = false) {
		if ($isConfig) {
			return parent::setTemplateDir($template, $isConfig);
		} else {
			return parent::setTemplateDir(self::appendUiDefaults($template, $this->uiTemplateDir));
		}
	}
	
	/**
	 * Set the directories where configs are stored
	 *
	 * Preserves default BootstrapSmarty config directory to allow for extensions
	 * and applications of the base configs by application configs.
	 *
	 * @param string|string[] $config Additional Smarty config directories
	 *
	 * @uses BootstrapSmarty::setTemplateDir() to effect directory-mapping
	 *
	 * @see http://www.smarty.net/docs/en/api.set.config.dir.tpl Smarty::setConfigDir()
	 **/
	public function setConfigDir($config) {
		return parent::setConfigDir(self::appendUiDefaults($config, $this->uiConfigDir));
	}
	
	/**
	 * Set the directory where compiled templates are stored
	 *
	 * Allows $compile to be empty (in which case BootstrapSmarty::$uiCompileDir 
	 * default is substituted for the empty value)
	 *
	 * @param string $compile Alternative Smarty compiled template directory
	 *
	 * @see http://www.smarty.net/docs/en/api.set.compile.dir.tpl Smarty::setCompileDir()
	 **/
	public function setCompileDir($compile) {
		return parent::setCompileDir(self::appendUiDefaults($compile, $this->uiCompileDir, false));
	}

	/**
	 * Set the directory where cache files are stored
	 *
	 * Allows $cache to be empty (in which case BootstrapSmarty::$uiCacheDir is
	 * substituted for the empty value)
	 *
	 * @param string $cache Alternative Smarty cache file directory
	 *
	 * @see http://www.smarty.net/docs/en/api.set.cache.dir.tpl Smarty::setCacheDir()
	 **/
	public function setCacheDir($cache) {
		return parent::setCacheDir(self::appendUiDefaults($cache, $this->uiCacheDir, false));
	}
	
	public function addTemplateDir($template, $key = null, $isConfig = false) {
		if ($isConfig) {
			return parent::addTemplateDir($template, $key, $isConfig);
		} else {
			if (!empty($key) && !empty($this->getTemplateDir($key))) {
				return parent::addTemplateDir($template, $key);
			} else {
				if (!empty($key)) {
					$template = array($key => $template);
				}
				return parent::setTemplateDir(self::appendUiDefaults($template, $this->getTemplateDir()));
			}
		}
	}

	public function addConfigDir($config, $key = null) {
		if (!empty($key) && !empty($this->getConfigDir($key))) {
			return parent::addConfigDir($template, $key);
		} else {
			if (!empty($key)) {
				$config = array($key => $config);
			}
			return parent::setConfigDir($self::appendUiDefaults($config, $this->getConfigDir()));
		}
	}
	
	/**
	 * Add additional CSS stylesheets
	 *
	 * Additional stylesheets are loaded after the base stylesheet(s)
	 *
	 * @param string|string[] $stylesheet URL(s) of additional stylesheets (with
	 *		optional associative array keys naming them)
	 * @param string $key (Optional) Identifying key for a single stylesheet
	 *		(Applied with numeric identifiers if $stylesheet is an array without its
	 *		own defined associative array keys). If $key already exists in the list of
	 *		stylesheets, that stylesheet is replaced by $stylesheet
	 *
	 * @throws BootstrapSmarty_Exception NOT_A_URL If $stylesheet is not a URL or an
	 *		array of URLs
	 **/
	public function addStylesheet($stylesheet, $key = null) {
		/* default to the APP_KEY if no key is set */
		$_key = self::APP_KEY;
		if (!empty($key)) {
			$_key = $key;
		}
		
		/* construct the array of additional stylesheets */
		$_stylesheet = array();
		/* Is $stylesheet an associative array? If so, just assume that the user knows
		   what they're doing (names, no names, whatevs).
		   http://stackoverflow.com/a/4254008/294171 */
		if (is_array($stylesheet) && count(array_filter(array_keys($stylesheet), 'is_string'))) {
			// FIXME actually test the array elements to see if they are URLs
			$_stylesheet = $stylesheet;
		} elseif (is_array($stylesheet)) { /* non-associative array */
			/* continue auto-numbering already started for this key */
			$counter = 1;
			foreach (array_keys($this->stylesheets) as $name => $s) {
				if (preg_match("/$_key-(\d+)/", $name, $match)) {
					$counter = max($counter, $match[1] + 1);
				}
			}
			foreach ($stylesheet as $s) {
				if (is_string($s)) {
					$_stylesheet["{$_key}-{$counter}"] = $s;
					$counter++;
				} else {
					throw new BootstrapSmarty_Exception(
						"'{$s}' is not a URL to a CSS stylesheet",
						BootstrapSmarty_Exception::NOT_A_URL
					);
				}
			}
		} elseif (is_string($stylesheet)) { /* single stylesheet url */
			$_stylesheet[$_key] = $stylesheet;
		} else {
			throw new BootstrapSmarty_Exception(
				"'$stylesheet' is not a URL to a CSS stylesheet",
				BootstrapSmarty_Exception::NOT_A_URL
			);
		}
		
		/* append or replace (if $key is not empty) stylesheets */
		$this->stylesheets = array_replace($this->stylesheets, $_stylesheet);
	}
	
	/**
	 * Return list of stylesheets, optionally matching $key
	 *
	 * If $key is empty, all stylesheets are returned.
	 *
	 * If $key is non-empty, both stylesheets matching $key exactly and stylesheets
	 * matching $key-##, where ## is an auto-generated numeric index, will be
	 * returned.
	 *
	 * @param string $key Name of stylesheet(s) to return
	 *
	 * @return string[] List of stylesheets matching $key
	 **/
	public function getStylesheet($key = null) {
		if (empty($key)) {
			return $this->stylesheets;
		} else {
			$result = array();
			foreach($this->stylesheets as $name => $value) {
				if (preg_match("/$key-?\d*/", $name)) {
					$result[$name] = $value;
				}
			}
			return $result;
		}
	}
	
	/**
	 * Add a message to be diplayed to the user
	 *
	 * @param string $title HTML-formatted title of the message
	 * @param string $content HTML-formatted content of the message
	 * @param string $class (Optional) CSS class name of the message ("message is
	 *		default value, "error" and "good" are also styled by default)
	 **/
	public function addMessage($title, $content, $class = NotificationMessage::INFO) {
		$this->messages[] = new NotificationMessage($title, $content, $class);
	}
	
	/**
	 * Add datepicker functionality
	 * 
	 * @param string $moduleName
	 *
	 * @return boolean `TRUE` on success, `FALSE` on failure
	 *
	 * @see https://github.com/eternicode/bootstrap-datepicker eternicode/bootstrap-datepicker
	 **/
	public function enable($moduleName) {
		switch ($moduleName) {
			case self::MODULE_DATEPICKER:
				$this->addStylesheet(dirname($this->getStylesheet(self::UI_KEY)[self::UI_KEY]) . '/bootstrap-datepicker.min.css', self::MODULE_DATEPICKER);
				// TODO probably should really have a JavaScript list like the Stylesheet list...
				return true;
			
			default:
				return false;
		}
	}

	/**
	 * Displays the template
	 *
	 * Overrides Smarty::display() to provide some built-in template variables,
	 * including stylesheets and messages.
	 *
	 * @param string $template (Optional) Name of template file (defaults to
	 *		'page.tpl')
	 * @param string $cache_id (Optional)
	 * @param string $compile_id (Optional)
	 * @param string $parent (Optional)
	 *
	 * @see http://www.smarty.net/docs/en/api.display.tpl Smarty::display()
	 **/
	public function display($template = 'page.tpl', $cache_id = null, $compile_id = null, $parent = null) {
		$this->assign('uiMessages', $this->messages);
		$this->assign('uiStylesheets', $this->stylesheets);
		parent::display($template, $cache_id, $compile_id, $parent);
	}
}

/**
 * All exceptions thrown by BootstrapSmarty
 *
 * @author Seth Battis <seth@battis.net>
 **/
class BootstrapSmarty_Exception extends \Exception {
	/** Violation of singleton design pattern */
	const SINGLETON = 1;
	
	/** A directory that needs to be readable is not */
	const UNREADABLE_DIRECTORY = 2;
	
	/** A directory that needs to be writable is not */
	const UNWRITABLE_DIRECTORY = 3;
	
	/** A file or directory that should exist does not */
	const MISSING_FILES = 4;
	
	/** A URL was expected, but not received */
	const NOT_A_URL = 5;
}
	
?>