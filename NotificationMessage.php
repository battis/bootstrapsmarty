<?php

class NotificationMessage {

	const ERROR = 'error';
	const GOOD = 'good';
	
	public $title= null;
	public $content = null;
	public $class = 'message';
	
	public function __construct($title, $content, $class = 'message') {
		$this->title = $title;
		$this->content = $content;
		$this->class = $class;
	}
}
	
?>