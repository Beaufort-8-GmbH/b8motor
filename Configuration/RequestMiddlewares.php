<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2018 - 2022 Feng Lu <lu@beaufort8.de>
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

return [
    'frontend' => [
        'typo3/cms-frontend/eid' => [
            'disabled' => true
        ],
        'typo3/cms-frontend/eid-new' => [
            'target' => \TYPO3\CMS\Frontend\Middleware\EidHandler::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ]
        ],
        'b8/b8motor/ajax' => [
            'target' => \B8\B8motor\Middleware\AjaxMiddleware::class,
            'after' => ['typo3/cms-frontend/prepare-tsfe-rendering']
        ]
    ],
];