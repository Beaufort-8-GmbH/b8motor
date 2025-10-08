<?php
declare(strict_types = 1);

namespace B8\B8motor\UserFunc;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 - 2025 Feng Lu <lu@beaufort8.de>
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


/**
 * Class FlexformUserFunc
 */
class FlexformUserFunc
{
    // protected $settings = array();

    // public function __construct()
    // {
    //     // $this->settings = $this->getSettingsFromYaml(\TYPO3\CMS\Core\Core\Environment::getProjectPath() . '/config/b8motor/basic/smartImage.yaml');

    // }

    /**
     * Build smart image component type dropdown list
     *
     * @param   array  &$fConfig  [&$fConfig description]
     *
     * @return  void
     */
    public function smartImageComponentList(array &$fConfig): void
    {
        $settings = $this->getSettingsFromYaml(\TYPO3\CMS\Core\Core\Environment::getProjectPath() . '/config/b8motor/basic/smartImage.yaml');

        // var_dump($fConfig['row']['uid_local'][0]['row'], $fConfig['row']);exit;

        if (count($settings) > 0 && count($settings['smartImageSettings']) > 0) {
            foreach ($settings['smartImageSettings'] as $key=>$setting) {
                // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($setting['plugin']);

                // if ($fConfig['row']['tablenames'] !== 'tt_content') { // image of plugin
                //     if ($setting['plugin'] === $fConfig['row']['tablenames']) {
                //         array_push($fConfig['items'], array(
                //             $setting['type'],
                //             $key
                //         ));
                //     }
                // } else { // image of content element
                    // if (is_null($setting['plugin'])) {
                        array_push($fConfig['items'], array(
                            $setting['type'],
                            $key
                        ));
                    // }
                // }
            }
        }

    }

    /**
     * Build smart image component style dropdown list
     *
     * @param   array  &$fConfig  [&$fConfig description]
     *
     * @return  void
     */
    public function smartImageComponentStyle(array &$fConfig): void
    {
        $settings = $this->getSettingsFromYaml(\TYPO3\CMS\Core\Core\Environment::getProjectPath() . '/config/b8motor/basic/smartImage.yaml');

        if (count($settings) > 0 && count($settings['smartImageSettings']) > 0) {
            if (empty($fConfig['row']['tx_b8motor_component_type'])) { // new image

                foreach ($settings['smartImageSettings'] as $key=>$setting) {
                    // if ($fConfig['row']['tablenames'] !== 'tt_content') { // image of plugin
                        // if ($setting['plugin'] === $fConfig['row']['tablenames']) {
                            foreach ($setting['style'] as $key=>$style) {
                                array_push($fConfig['items'], array(
                                    $style['name'],
                                    $key
                                ));
                            }
                            break;
                    //     }
                    // } else { // image of content element
                    //     if (is_null($setting['plugin'])) {
                    //         foreach ($setting['style'] as $key=>$style) {
                    //             array_push($fConfig['items'], array(
                    //                 $style['name'],
                    //                 $key
                    //             ));
                    //         }
                    //         break;
                    //     }
                    // }
                }
            } else {
                foreach ($settings['smartImageSettings'][$fConfig['row']['tx_b8motor_component_type'][0]]['style'] as $key=>$style) {
                    array_push($fConfig['items'], array(
                        $style['name'],
                        $key
                    ));
                }

            }
        }
    }

    /**
     * get settings from yaml file
     *
     * @param   string  $file  file path
     *
     * @return  array          settings
     */
    protected function getSettingsFromYaml(string $file): array
    {
        $settings = array();

        if (is_file($file)) {
            $yamlFileLoader  = GeneralUtility::makeInstance(YamlFileLoader::class);
            $settings        = $yamlFileLoader->load($file);
        } else {
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $messageQueue        = $flashMessageService->getMessageQueueByIdentifier();
            $severity            = ContextualFeedbackSeverity::ERROR;
            $prompt              = 'Configration file "' . $file .'" is missing.';
            $title               = 'Load SmartImage Configuration';
            $message             = GeneralUtility::makeInstance(FlashMessage::class, $prompt, $title, $severity, false); // NOTICE, INFO, OK, WARNING, ERROR

            $messageQueue->enqueue($message);

        }

        return $settings;
    }
}