<?php
declare(strict_types = 1);

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

namespace B8\B8motor\ViewHelpers;

class PidFromLinkViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('link', 'string', 'The page link to resolve the viewhelper for', true);
    }

    public function render(): string
    {
        return str_replace( 't3://page?uid=', '', $this->arguments['link'] );
    }
}
