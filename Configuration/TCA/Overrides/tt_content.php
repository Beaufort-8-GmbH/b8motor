<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2018 - 2022 Feng Lu <lu@beaufort8.de>
*  All rights reserved
*
*  This file is part of the "B8 Motor" Extension for TYPO3 CMS.
*  The TYPO3 project is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License as published
*  by the Free Software Foundation; either version 2 of the License, or
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

defined('TYPO3_MODE') or die();

(function () {
    // plugin signature: <extension name without underscores> '_' <plugin name in lowercase>
    $pluginsignatur = 'b8motor_basic';

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginsignatur] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        $pluginsignatur,
        // Flexform configuration schema file
        'FILE:EXT:b8motor/Configuration/FlexForms/Registration.xml'
    );



    /* ============================ sys_file_reference modify ============================ */
    $newSystemFileReferenceElement = [
        'tx_b8motor_as_responsive' => [
            'exclude' => 0,
            'label'   => 'Responsive Image?',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Yes', 0],
                    ['No',  1],
                ],
                'size'     => 1,
                'maxitems' => 1,
                'eval'     => '',
                'default'  => 0,
            ],
            'onChange' => 'reload',
        ],
        'tx_b8motor_component_type' => [
            'exclude' => 0,
            'label'   => 'Image for Component',
            'config'  => [
                'type'  => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => B8\B8motor\UserFunc\FlexformUserFunc::class . '->smartImageComponentList',
                'parameters' => '',
                'size'     => 1,
                'maxitems' => 1,
                'eval'     => '',
                'default'  => '',
            ],
            'onChange' => 'reload',
            'displayCond' => [
                'OR' => [
                    'FIELD:tx_b8motor_as_responsive:REQ:FALSE',
                    'FIELD:tx_b8motor_as_responsive:=:0',
                ]
            ],
        ],
        'tx_b8motor_component_style' => [
            'exclude' => 0,
            'label'   => 'Component Style',
            'config'  => [
                'type'  => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => B8\B8motor\UserFunc\FlexformUserFunc::class . '->smartImageComponentStyle',
                'parameters' => '',
                'size'     => 1,
                'maxitems' => 1,
                'eval'     => '',
                'default'  => 0,
            ],
            'onChange' => 'reload',
            'displayCond' => [
                'OR' => [
                    'FIELD:tx_b8motor_as_responsive:REQ:FALSE',
                    'FIELD:tx_b8motor_as_responsive:=:0',
                ]
            ],
        ],
    ];
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_reference', $newSystemFileReferenceElement);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'sys_file_reference',
        'tx_b8motor_as_responsive, tx_b8motor_component_type, tx_b8motor_component_style'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        'sys_file_reference',
        'imageoverlayPalette',
        '--linebreak--',
        'after:crop'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        'sys_file_reference',
        'imageoverlayPalette',
        'tx_b8motor_as_responsive'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        'sys_file_reference',
        'imageoverlayPalette',
        'tx_b8motor_component_type',
        'after:tx_b8motor_as_responsive'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        'sys_file_reference',
        'imageoverlayPalette',
        'tx_b8motor_component_style',
        'after:tx_b8motor_component_type'
    );

    unset($newSystemFileReferenceElement);
    /* ============================ /sys_file_reference modify ============================ */

})();