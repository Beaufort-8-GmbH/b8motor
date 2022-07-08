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


class UrlHelper
{
    protected static $from = array(
            'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ',
            'Ç',
            'È', 'É', 'Ê', 'Ë',
            'Ì', 'Í', 'Î', 'Ï',
            'Ð',
            'Ñ',
            'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü',
            'Ý',
            'ß',
            'à', 'á', 'â', 'ã', 'ä', 'å', 'æ',
            'ç',
            'è', 'é', 'ê', 'ë',
            'ì', 'í', 'î', 'ï',
            'ñ',
            'ò', 'ó', 'ô', 'õ', 'ö', 'ø',
            'ù', 'ú', 'û', 'ü',
            'ý', 'ÿ',
            'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą',
            'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č',
            'Ď', 'ď', 'Đ', 'đ',
            'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě',
            'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ',
            'Ĥ', 'ĥ', 'Ħ', 'ħ',
            'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ',
            'Ĵ', 'ĵ',
            'Ķ', 'ķ',
            'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł',
            'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ',
            'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ',
            'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř',
            'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š',
            'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ',
            'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų',
            'Ŵ', 'ŵ',
            'Ŷ', 'ŷ', 'Ÿ',
            'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž',
            'ſ',
            'ƒ',
            'Ơ', 'ơ',
            'Ư', 'ư',
            'Ǎ', 'ǎ',
            'Ǐ', 'ǐ',
            'Ǒ', 'ǒ',
            'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ',
            'Ǻ', 'ǻ', 'Ǽ', 'ǽ',
            'Ǿ', 'ǿ',
            '\'', '"', '|');
    protected static $to = array(
            'A', 'A', 'A', 'A', 'Ae', 'A', 'AE',
            'C',
            'E', 'E', 'E', 'E',
            'I', 'I', 'I', 'I',
            'D',
            'N',
            'O', 'O', 'O', 'O', 'Oe', 'O',
            'U', 'U', 'U', 'Ue',
            'Y',
            'ss',
            'a', 'a', 'a', 'a', 'ae', 'a', 'ae',
            'c',
            'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i',
            'n',
            'o', 'o', 'o', 'o', 'oe', 'o',
            'u', 'u', 'u', 'ue',
            'y', 'y',
            'A', 'a', 'A', 'a', 'A', 'a',
            'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c',
            'D', 'd', 'D', 'd',
            'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e',
            'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g',
            'H', 'h', 'H', 'h',
            'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij',
            'J', 'j',
            'K', 'k',
            'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l',
            'N', 'n', 'N', 'n', 'N', 'n', 'n',
            'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe',
            'R', 'r', 'R', 'r', 'R', 'r',
            'S', 's', 'S', 's', 'S', 's', 'S', 's',
            'T', 't', 'T', 't', 'T', 't',
            'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
            'W', 'w',
            'Y', 'y', 'Y',
            'Z', 'z', 'Z', 'z', 'Z', 'z',
            's',
            'f',
            'O', 'o',
            'U', 'u',
            'A', 'a',
            'I', 'i',
            'O', 'o',
            'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
            'A', 'a', 'AE', 'ae',
            'O', 'o',
            '', '', '');

    protected static function sameLength(array $from, array $to): bool
    {
        return count($from) === count($to);
    }

    public static function setStringFrom(array $strArray): void
    {
        self::$from = $strArray;
    }

    public static function setStringTo(array $strArray): void
    {
        self::$to = $strArray;
    }

    public static function sanitize(string $url): string
    {
        if (!self::sameLength(self::$from, self::$to)) {
            throw new \Exception('Url sanitize error, please check the lenght of patterns.');
        }

        // return url like: abc-123
        return preg_replace('/[^\p{L}\p{N}]/u', '-', str_replace(self::$from, self::$to, strtolower($url)));
    }
}