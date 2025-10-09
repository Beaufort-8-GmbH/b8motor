<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2018 - 2025 Feng Lu <lu@beaufort8.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/



(function () {
    /**
     * Configure Extbase plugin 'Basic' for extension 'B8motor' as a dedicated CType.
     * This adds the required fifth parameter ($pluginType) to avoid Deprecation #105076 in TYPO3 v13+,
     * and ensures future compatibility with TYPO3 v14.
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'B8.B8motor',    // format: <vendor name>.<extension name>
        'Basic',         // format: <plugin name>
        [
        ],
        // non-cacheable actions
        [
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );
    /*
     * Plugin Icon
     */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'B8_Logo',
        \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        ['source' => 'EXT:b8motor/Resources/Public/Icons/Extension.png']
    );

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['b8motor'] = B8\B8motor\Hooks\SmartImageHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['b8motor']  = B8\B8motor\Hooks\SmartImageHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][B8\B8motor\Scheduler\SmartImageHealthCheck::class] = array(
        'extension'        => 'b8motor',
        'title'            => 'Smart Image Health Check',
        'description'      => 'Check image contents and related files',
        'additionalFields' => ''
    );


})();
