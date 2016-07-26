<?php

namespace Battis\BootstrapSmarty;

/**
 * All exceptions thrown by BootstrapSmarty
 *
 * @author Seth Battis <seth@battis.net>
 **/
class BootstrapSmarty_Exception extends \Exception
{
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
