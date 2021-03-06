<?php

namespace Battis\BootstrapSmarty;

use Battis\DataUtilities;

/**
 * A wrapper for Smarty to set (and maintain) defaults within a Bootstrap
 * UI environment
 *
 * @author Seth Battis <seth@battis.net>
 **/
class BootstrapSmarty extends \Smarty
{
    /**
     * @var BootstrapSmarty|NULL Reference to the singleton BootstrapSmarty
     *        instance
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

    /**
     * Module name for eternicode/bootstrap-datepicker
     */
    const MODULE_DATEPICKER = 'eternicode/bootstrap-datepicker';

    /**
     * Module name for mjolnic/bootstrap-colorpicker
     */
    const MODULE_COLORPICKER = 'mjolnic/bootstrap-colorpicker';

    /**
     * Module name for drvic10k/bootstrap-sortable
     */
    const MODULE_SORTABLE = 'drvic10k/bootstrap-sortable';

    /**
     * @var NotificationMessage[] List of pending notification messages
     *        to be displayed
     **/
    private $messages = [];

    /** @var string[] $stylesheets List of stylesheets to be applied */
    private $stylesheets = [];

    /** @var string[] $scripts List of Javascript files to be loaded */
    private $scripts = [];

    /** @var string[] #scriptSnippets List of Javascript snippets to be run after scripts are loaded */
    private $scriptSnippets = [];

    /** var string $url URL of BootstrapSmarty instance */
    private $url;

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
     * @return boolean TRUE if the directory is writable
     *
     * @throws BootstrapSmarty_Exception UNWRITABLE_DIRECTORY If the directory is not
     *        writable
     **/
    private static function testAccess($directory, $writable = false)
    {
        $success = false;
        if (file_exists($directory)) {
            if (is_dir($directory)) {
                if (is_readable($directory)) {
                    if (!$writable || ($writable && is_writable($directory))) {
                        $success = true;
                    }
                } else {
                    if ($writable) {
                        $success = chmod($directory, 0770);
                    } else {
                        $success = chmod($directory, 0550);
                    }
                }
            }
        } else {
            $success = mkdir($directory);
            if ($success && $writable) {
                $success = chmod($directory, 0550);
            }
        }

        if (!$success) {
            throw new BootstrapSmarty_Exception(
                "The directory '{$directory}' cannot be created or cannot be made writable",
                BootstrapSmarty_Exception::UNWRITABLE_DIRECTORY
            );
        }
    }

    /**
     * Return the singleton instance of BootstrapSmarty
     *
     * @param string|string[] $template (Optional) Additional Smarty template
     *        directories
     * @param string|string[] $config (Optional) Additional Smarty config
     *        directories
     * @param string $compile (Optional) Alternative Smarty compiled template
     *        directory
     * @param string $cache (Optional) Alternative Smarty cache directory
     *
     * @return BootstrapSmarty
     *
     * @see http://www.phptherightway.com/pages/Design-Patterns.html#singleton Singleton Design Pattern
     **/
    public static function getSmarty($template = null, $config = null, $compile = null, $cache = null)
    {
        if (static::$singleton === null) {
            static::$singleton = new static($template, $config, $compile, $cache);
        }
        return static::$singleton;
    }

    /**
     * Construct the singleton instance of BootstrapSmarty
     *
     * @deprecated Use singleton pattern BootstrapSmarty::getSmarty()
     *
     * @param string|string[] $template (Optional) Additional Smarty template
     *        directories
     * @param string|string[] $config (Optional) Additional Smarty config
     *        directories
     * @param string $compile (Optional) Alternative Smarty compiled template
     *        directory
     * @param string $cache (Optional) Alternative Smarty cache directory
     *
     * @return void
     *
     * @throws BootstrapSmarty_Exception SINGLETON If an instance of BootstrapSmarty already exists
     *
     * @see BootstrapSmarty::getSmarty() BootstrapSmarty::getSmarty()
     * @see http://www.phptherightway.com/pages/Design-Patterns.html#singleton Singleton Design Pattern
     **/
    public function __construct($template = null, $config = null, $compile = null, $cache = null)
    {
        if (static::$singleton !== null) {
            throw new BootstrapSmarty_Exception(
                'BootstrapSmarty is a singleton class, use the factory method ' .
                    'BootstrapSmarty::getSmarty() instead of ' . __METHOD__,
                BootstrapSmarty_Exception::SINGLETON
            );
        } else {
            parent::__construct();
            static::$singleton = $this;
        }

        /* Default to local directories for use by Smarty */
        $this->setTemplateDir([static::UI_KEY => realpath(__DIR__ . '/../templates')]);
        $this->setConfigDir([static::UI_KEY => realpath(__DIR__ . '/../configs')]);

        /* Apply user additions and alternates */
        if (!empty($template)) {
            $this->prependTemplateDir($template);
        }
        if (!empty($config)) {
            $this->addConfigDir($config);
        }
        $this->setCompileDir((
            empty($compile) ?
                realpath(__DIR__ . '/../templates_c') :
                $compile
        ));
        $this->setCacheDir((
            empty($cache) ?
                realpath(__DIR__ . '/../cache') :
                $cache
        ));

        /* Test all directories for use by Smarty */
        foreach ($this->getTemplateDir() as $key => $dir) {
            static::testAccess($dir);
        }
        foreach ($this->getConfigDir() as $key => $dir) {
            static::testAccess($dir);
        }
        static::testAccess($this->getCompileDir(), true);
        static::testAccess($this->getCacheDir(), true);

        /* set some reasonable defaults */
        $this->url = DataUtilities::URLfromPath(dirname(__DIR__));
        $this->assign('BOOTSTRAPSMARTY_URL', $this->url);
        $this->addStylesheet("{$this->url}/css/BootstrapSmarty.css", static::UI_KEY);
        $this->assign([
            'name' => DataUtilities::titleCase(
                preg_replace(
                    '/[\-_]+/',
                    ' ',
                    urldecode(basename($_SERVER['REQUEST_URI'], '.php'))
                )
            ),
            'category' => DataUtilities::titleCase(
                preg_replace(
                    '/[\-_]+/',
                    ' ',
                    urldecode(basename(dirname($_SERVER['REQUEST_URI'])))
                )
            ),
            'navbarActive' => false,
            'MODULE_COLORPICKER' => static::MODULE_COLORPICKER,
            'MODULE_DATEPICKER' => static::MODULE_DATEPICKER,
            'MODULE_SORTABLE' => static::MODULE_SORTABLE
        ]);
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
    private function __clone()
    {
        throw new BootstrapSmarty_Exception(
            'BootstrapSmarty is a singleton class, use the factory method ' .
                'BootstrapSmarty::getSmarty() instead of ' . __METHOD__,
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
    private function __wakeup()
    {
        throw new BootstrapSmarty_Exception(
            'BootstrapSmarty is a singleton class, use the factory method ' .
                'BootstrapSmarty::getSmarty() instead of ' . __METHOD__,
            BootstrapSmarty_Exception::SINGLETON
        );
    }

    /**
     * Prepend a template directory to the list of template directories
     *
     * Smarty searches the list of template directories for a particular
     * template in the order in which the directories are listed and uses the
     * first template encountered. Thus, if you want to override an existing
     * template file, your overriding template directory needs to be prepended
     * to that list.
     *
     * @param string $template Path to template directory
     * @param string $key (Optional) Unique identifier for template directory
     * @return BootstrapSmarty Current Smarty instance for chaining
     */
    public function prependTemplateDir($template, $key = false) {
        $templates = $this->getTemplateDir();
        if (empty($key)) {
            $this->setTemplateDir($template);
        } else {
            $this->setTemplateDir([$key => $template]);
        }
        $this->addTemplateDir($templates);
        return $this;
    }

    /**
     * Add additional CSS stylesheet
     *
     * Additional stylesheets are loaded after the base stylesheet(s)
     *
     * @param string|string[] $stylesheet URL(s) of additional stylesheets (with
     *        optional associative array keys naming them)
     * @param string $key (Optional) Identifying key for a single stylesheet
     *        (Applied with numeric identifiers if $stylesheet is an array without its
     *        own defined associative array keys). If $key already exists in the list of
     *        stylesheets, that stylesheet is replaced by $stylesheet
     *
     * @throws BootstrapSmarty_Exception NOT_A_URL If $stylesheet is not a URL or an
     *        array of URLs
     **/
    public function addStylesheet($stylesheet, $key = null)
    {
        /* default to the APP_KEY if no key is set */
        $_key = static::APP_KEY;
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
    public function getStylesheet($key = null)
    {
        if (empty($key)) {
            return $this->stylesheets;
        } else {
            $result = array();
            foreach ($this->stylesheets as $name => $value) {
                if (preg_match("/$key-?\d*/", $name)) {
                    $result[$name] = $value;
                }
            }
            return $result;
        }
    }

    /**
     * Add a script to the list to be loaded after Bootstrap and JQuery
     *
     * @param string $script URL of the script file
     * @param string $key (Optional) Unique identifier for the script
     **/
    public function addScript($script, $key = null)
    {
        if (empty($key)) {
            $this->scripts[] = $script;
        } else {
            $this->scripts[$key] = $script;
        }
    }

    /**
     * Add a snippet of Javascript to run after script files are loaded
     *
     * @param string $snippet Javascript snippet
     * @param string $key (Optional) Unique identifier for the snippet
     **/
    public function addScriptSnippet($snippet, $key = null)
    {
        if (empty($key)) {
            $this->scriptSnippets[] = $snippet;
        } else {
            $this->scriptSnippets[$key] = $snippet;
        }
    }

    /**
     * Add a message to be diplayed to the user
     *
     * @param string $title HTML-formatted title of the message
     * @param string $content HTML-formatted content of the message
     * @param string $class (Optional) CSS class name of the message ("message is
     *        default value, "error" and "good" are also styled by default)
     **/
    public function addMessage($title, $content, $class = NotificationMessage::INFO)
    {
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
    public function enable($moduleName)
    {
        $assetUrl = $this->url . (preg_match('|/vendor/|', __DIR__) ? '/../..' : '/vendor');
        switch ($moduleName) {
            case static::MODULE_DATEPICKER:
                $this->addStylesheet(
                    "$assetUrl/bower-asset/bootstrap-datepicker-1.x/dist/css/bootstrap-datepicker.min.css",
                    static::MODULE_DATEPICKER
                );
                $this->addScript(
                    "$assetUrl/bower-asset/bootstrap-datepicker-1.x/dist/js/bootstrap-datepicker.min.js",
                    static::MODULE_DATEPICKER
                );
                $this->addScriptSnippet("
                    $('.input-group.date').datepicker({
                        orientation: 'top auto',
                        autoclose: true,
                        todayHighlight: true
                    });
                ", static::MODULE_DATEPICKER);
                return true;

            case static::MODULE_COLORPICKER:
                $this->addStylesheet(
                    "$assetUrl/bower-asset/bootstrap-colorpicker-2.x/dist/css/bootstrap-colorpicker.min.css",
                    static::MODULE_COLORPICKER
                );
                $this->addScript(
                    "$assetUrl/bower-asset/bootstrap-colorpicker-2.x/dist/js/bootstrap-colorpicker.min.js",
                    static::MODULE_COLORPICKER
                );
                $this->addScriptSnippet("
                    $('.input-group.color').colorpicker();
                ", static::MODULE_COLORPICKER);
                return true;

            case static::MODULE_SORTABLE:
                $this->addStylesheet(
                    "$assetUrl/bower-asset/bootstrap-sortable-1.x/Contents/bootstrap-sortable.css",
                    static::MODULE_SORTABLE
                );
                $this->addScript(
                    "$assetUrl/bower-asset/moment/min/moment.min.js",
                    'required by ' . static::MODULE_SORTABLE
                );
                $this->addScript(
                    "$assetUrl/bower-asset/bootstrap-sortable-1.x/Scripts/bootstrap-sortable.js",
                    static::MODULE_SORTABLE
                );
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
     *        'page.tpl')
     * @param string $cache_id (Optional)
     * @param string $compile_id (Optional)
     * @param string $parent (Optional)
     *
     * @see http://www.smarty.net/docs/en/api.display.tpl Smarty::display()
     **/
    public function display($template = 'page.tpl', $cache_id = null, $compile_id = null, $parent = null)
    {
        $this->assign([
            'uiMessages' => $this->messages,
            'uiStylesheets' => $this->stylesheets,
            'uiScripts' => $this->scripts,
            'uiScriptSnippets' => $this->scriptSnippets
        ]);
        parent::display($template, $cache_id, $compile_id, $parent);
    }
}
