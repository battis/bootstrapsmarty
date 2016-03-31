<?php

require_once '../vendor/autoload.php';

use \Battis\BootstrapSmarty\BootstrapSmarty;
use \Battis\BootstrapSmarty\NotificationMessage;

$ui = BootstrapSmarty::getSmarty(false);
$ui->addTemplateDir(__DIR__ . '/templates');

//$ui->addMessage('foo', '<a href="#">link1</a> <a class="test" href="#">link2</a> not link', NotificationMessage::ERROR);
//$ui->addMessage('foo', 'bar', NotificationMessage::GOOD);
//$ui->addMessage('<a href="#">link</a> not link', 'another message');

$ui->assign('formAction', $_SERVER['PHP_SELF']);

$ui->enable(BootstrapSmarty::MODULE_DATEPICKER);
$ui->enable(BootstrapSmarty::MODULE_COLORPICKER);
$ui->enable(BootstrapSmarty::MODULE_SORTABLE);

$ui->display("form-page.tpl");
	
?>