<?php
declare(strict_types=1);

namespace B8\B8motor\Controller;

use B8\B8motor\Interfaces\SmartImageInterface;

/***************************************************************
*  Copyright notice
*
*  (c) 2020 Feng Lu <lu@beaufort8.de>
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

class SmartImageController
{
    /**
     * @var SmartImageInterface
     */
    protected $smartImage;

    /**
     * __construct
     *
     * @param   SmartImageInterface  $smartImage   interface
     *
     */
    public function __construct(SmartImageInterface $smartImage)
    {
        $this->smartImage = $smartImage;
    }

    /**
     * render picture element
     *
     * @param     string    $cid           content id or db record id
     * @param     string    $tablenames    field "tablenames" in table "sys_file_reference"
     * @param     string    $path          path name, e.g. '' or 'contact'
     *
     * @return  string  html component picture
     */
    public function renderImage(int $cid, string $tablenames, string $path): string
    {
        return $this->smartImage->renderImage($cid, $tablenames, $path);
    }
}