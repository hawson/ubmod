<?php
/**
 * Define constants used by the application.
 *
 * @author Jeffrey T. Palmer <jtpalmer@ccr.buffalo.edu>
 * @version $Id$
 * @copyright Center for Computational Research, University at Buffalo, 2011
 * @package UBMoD
 */

/**
 * UBMoD application base directory.
 */
define("BASE_DIR", dirname(dirname(__FILE__)));

/**
 * Class file directory.
 */
define("LIB_DIR", BASE_DIR . "/lib");

/**
 * Template directory.
 */
define("TEMPLATE_DIR", BASE_DIR . "/templates");

/**
 * Configuration file directory.
 */
define("CONFIG_DIR", BASE_DIR . "/config");

/**
 * Configuration file path.
 */
define("CONFIG_FILE", CONFIG_DIR . "/settings.ini");

/**
 * UBMoD version string.
 */
define("UBMOD_VERSION", "0.2.0");