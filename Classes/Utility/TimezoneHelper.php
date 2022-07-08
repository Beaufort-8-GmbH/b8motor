<?php
declare(strict_types = 1);

namespace B8\B8motor\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2019 - 2022 Feng Lu <lu@beaufort8.de>
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

class TimezoneHelper
{
    const TIMEZONE_NAMES = array(
            'en' => array(
                -12 => 'IDLW',
                -11 => 'NT',
                -10 => 'AHST',
                -9  => 'YST',
                -8  => 'PST',
                -7  => 'MST',
                -6  => 'CST',
                -5  => 'EST',
                -4  => 'AST',
                -3  => 'ADT',
                -2  => 'AT',
                -1  => 'WAT',
                0   => 'GMT',
                1   => 'CET',
                2   => 'EET',
                3   => 'MSK',
                4   => 'IOT',
                5   => 'EIT',
                6   => 'ICT',
                7   => 'WAST',
                8   => 'CCT',
                9   => 'JST',
                10  => 'EAST',
                11  => 'EDT',
                12  => 'IDLE',
            ),
            'de' => array(
                -12 => 'IDLW',
                -11 => 'NT',
                -10 => 'AHST',
                -9  => 'YST',
                -8  => 'PST',
                -7  => 'MST',
                -6  => 'CST',
                -5  => 'EST',
                -4  => 'AST',
                -3  => 'ADT',
                -2  => 'AT',
                -1  => 'WAT',
                0   => 'WEZ',
                1   => 'MEZ',
                2   => 'EET',
                3   => 'MSK',
                4   => 'IOT',
                5   => 'EIT',
                6   => 'ICT',
                7   => 'WAST',
                8   => 'CCT',
                9   => 'JST',
                10  => 'EAST',
                11  => 'EDT',
                12  => 'IDLE',
            ),
        );

    public static function getName(string $lang, int $id): string
    {
        return self::TIMEZONE_NAMES[$lang][$id];
    }

}