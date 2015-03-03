<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Isotope_alsobought
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'Contao\ModuleIsotopeAlsoBought' => 'system/modules/isotope_alsobought/ModuleIsotopeAlsoBought.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_iso_alsobought' => 'system/modules/isotope_alsobought/templates',
));
