<?php
declare(strict_types=1);

namespace B8\B8motor\Service;

use B8\B8motor\Interfaces\AjaxInterface;

/***************************************************************
*  Copyright notice
*
*  (c) 2020 - 2022 Feng Lu <lu@beaufort8.de>
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

class AjaxService implements AjaxInterface
{
    /**
     * Pasre query string arguments
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * @param     string    arguments from url in json format
     *
     * @return    array
     */
    public function parseParamsFromUrlRequest(string $param): array
    {
        if (strlen(urldecode($param)) >= 1024) { // parameter size less than 1024 chars
            return [];
        }

        $ret = json_decode(urldecode($param), true, JSON_UNESCAPED_UNICODE);
        return empty($ret) ? [] : $ret;
    }
}