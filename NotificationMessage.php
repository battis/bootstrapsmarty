<?php
/** NotificationMessage and related classes */

namespace Battis\BootstrapSmarty;

/**
 * A notification message for the user
 *
 * @author Seth Battis <seth@battis.net>
 **/
class NotificationMessage {

	/** Default CSS class selector for message */
	const INFO = 'alert-info';
		
	/**
	 * Alias for INFO
	 *
	 * @deprecated Use `INFO` instead for consistency with Bootstrap
	 **/
	const MESSAGE = 'alert-info';
	
	/** CSS class selector for an error message */
	const DANGER = 'alert-danger';
	
	/**
	 * Alias for DANGER
	 *
	 * @deprecated Use `DANGER` instead for consistency with Bootstrap
	 **/
	const ERROR = 'alert-danger';
	
	/** CSS class selector for a "good" (indiciating successful operation) message */
	const SUCCESS = 'alert-success';
	
	/**
	 * Alias for SUCCESS
	 *
	 * @deprecated Use `SUCCESS` instead for consistency with Bootstrap
	 **/
	const GOOD = 'alert-success';
	
	/** CSS class selector for an important message */
	const WARNING = 'alert-warning';
	
	/** @var string $title HTML-formatted title of the message */
	public $title = null;
	
	/** @var string $content HTML-formatted content of the message */
	public $content = null;
	
	/** @var string $class CSS class selector of the message */
	public $class = self::MESSAGE;
	
	/**
	 * Add Bootstrap Alert-link styling to links in the notification
	 *
	 * One annoying side-effect is that the HTML content will be re-wrapped in
	 * another `<div>` tag, just to ensure that `SimpleXML` can parse it.
	 *
	 * @param string $html The HTML content to be modified
	 *
	 * @return string All links in `$html` will be updated to include the `alert-link` selector class
	 **/
	private static function styleAlertLinks($html) {
		$dom = new DOMDocument;
		$dom->loadHTML($html);
		foreach($dom->getElementsByTagName('a') as $a) {
			$class = 'alert-link';
			if($a->hasAttribute('class')) {
				$class .= ' ' . $a->getAttribute('class');
			}
			$a->setAttribute('class', $class);
		}
		return $dom->saveHTML();
	}
	
	/**
	 * Construct a new notification message
	 *
	 * @param string $title
	 * @param string $content
	 * @param string $class (Optional) Defaults to NotificationMessage::MESSAGE
	 *
	 * @return void
	 **/
	public function __construct($title, $content, $class = self::INFO) {
		$this->title = NotificationMessage::styleAlertLinks($title);
		$this->content = NotificationMessage::styleAlertLinks($content);
		$this->class = $class;
	}
}
	
?>