<?php
declare(strict_types = 1);

namespace B8\B8motor\ViewHelpers;

use B8\B8motor\Service\SmartImageService;

/***************************************************************
*  Copyright notice
*
*  (c) 2018-2020 Feng Lu <lu@beaufort8.de>
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

class SmartImageViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    public function render(): string
    {
        $data = $this->templateVariableContainer->get('data');

        $cid = empty($data['_LOCALIZED_UID']) ? (int)$data['uid'] : (int)$data['_LOCALIZED_UID']; // content id

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);

        $smartImageService    = $objectManager->get(SmartImageService::class);
        $smartImageController = $objectManager->get(\B8\B8motor\Controller\SmartImageController::class, $smartImageService);


        return $smartImageController->renderImage($cid, 'tt_content', '');
    }

}
