<?php
declare(strict_types = 1);

namespace B8\B8motor\Utility;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;

/***************************************************************
*  Copyright notice
*
*  (c) 2020 - 2022 Feng Lu <lu@beaufort8.de>
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


class PageHelper extends AbstractPageTitleProvider
{
    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}