<?php
declare(strict_types = 1);

namespace B8\B8motor\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

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

class ImageHelper
{
    protected static $dest;
    protected static $orgFilepath;
    protected static $tmpFilepath;
    protected static $limitWidth;
    protected static $bkpAmount;

    /**
     * set path for responsive images
     *
     * @param   string     $_dest     path
     *
     * @return  void
     */
    public static function setDest(string $_dest): void
    {
        self::$dest = $_dest;
        if (!is_dir($_dest)) {
            try {
                mkdir($_dest, 0775, true);
            } catch (\Exception $e) {
                throw new \Exception('Can\'t build target directory "' . $_dest . '"');
            }
        }
    }

    /**
     * set uploaded image path
     *
     * @param   string     $_path     path of original image
     *
     * @return  void
     */
    public static function setOrgFilepath(string $_path): void
    {
        self::$orgFilepath = $_path;
        if (!is_dir($_path)) {
            try {
                mkdir($_path, 0775, true);
            } catch (\Exception $e) {
                throw new \Exception('Can\'t build original directory "' . $_path . '"');
            }
        }
    }

    /**
     * set path where the temporary image will be saved
     *
     * @param   string     $_path     path of temporary image
     *
     * @return  void
     */
    public static function setTmpFilepath(string $_path): void
    {
        self::$tmpFilepath = $_path;
        if (!is_dir($_path)) {
            try {
                mkdir($_path, 0775, true);
            } catch (\Exception $e) {
                throw new \Exception('Can\'t build temp directory "' . $_path . '"');
            }
        }
    }

    /**
     * set min. width of responsive image
     *
     * @param   int     $_w     width
     *
     * @return  void
     */
    public static function setLimitWidth(int $_w): void
    {
        self::$limitWidth = $_w;
    }

    /**
     * set amount of responsive images (e.g. 5 = 5 + 1)
     *
     * @param   int     $_a     amount
     *
     * @return  void
     */
    public static function setBreakpointsAmount(int $_a): void
    {
        self::$bkpAmount = $_a;
    }

    /**
     * generate the responsive images
     *
     * @param   array     $res        original(uploaded) image infos
     * @param   int       $id         content ID (tt_content.uid)
     * @param   int       $imgId      image ID (sys_file_reference.uid)
     *
     * @return  int                   the number of affected rows
     */
    public static function makeBreakpoints(array $res, int $id, int $imgId): int
    {
        $vars = array();

        $files = array();

        // Info about the source image
        $vars['source']   = self::$orgFilepath . $res['identifier'];
        $source_extension = $res['extension'];
        $source_filename  = substr($res['name'], 0, strlen($res['name'])-strlen($res['extension'])-1);

        $source_filesize  = $res['size'];
        $source_width     = $res['width'];
        $source_height    = $res['height'];

        $vars['upper'] = $source_width . 'x' . $source_height;
        $vars['lower'] = self::$limitWidth . 'x' . round($source_height * self::$limitWidth / $source_width);

        $vars['step']  = round((int)$source_filesize/self::$bkpAmount) + pow(10, strlen((string)$source_filesize)-3);

        list($upper_width, $upper_height) = explode('x', $vars['upper']);
        if ($upper_width > $source_width || $upper_height > $source_height) {
            $upper_width   = $source_width;
            $upper_height  = $source_height;
            $vars['upper'] = $source_width . 'x' . $source_height;
            throw new \Exception('upper size limit restricted to the source image size of ' . $vars['upper']);
        }

        list($lower_width, $lower_height) = explode('x', $vars['lower']);
        if ($lower_width > $upper_width || $lower_height > $upper_height) {
            $lower_width   = $upper_width;
            $lower_height  = $upper_height;
            $vars['lower'] = $upper_width . 'x' . $upper_height;
            throw new \Exception('lower size limit restricted to the upper image size of ' . $vars['lower']);
        }

        $temp = self::$tmpFilepath . $source_filename . '-temp.' . $source_extension;
        exec( escapeshellcmd(implode(' ', array('convert', '-scale', $vars['lower'], '-density', '72', '-quality', '50', $vars['source'], $temp))) );
        if ($source_extension === 'png') {
            exec(escapeshellcmd(implode(' ', array('convert', '-colors', '64', $temp, $temp))));
        }
        $lower_filesize = filesize($temp);
        $lower_size     = getimagesize($temp);
        $lower_width    = $lower_size[0];
        $lower_height   = $lower_size[1];

        $destination    = self::$dest . $source_filename . '-' . $lower_width . 'x' . $lower_height . '.' . $source_extension;
        if (!is_file($destination)) {
            rename($temp, $destination);
        }
        $files[] = [
            'name'   => $source_filename . '-' . $lower_width . 'x' . $lower_height . '.' . $source_extension,
            'width'  => $lower_width,
            'height' => $lower_height,
        ];

        $width_factor  = floor($vars['step'] * ($source_width - $lower_width) / ($source_filesize - $lower_filesize));
        $height_factor = floor($vars['step'] * ($source_height - $lower_height) / ($source_filesize - $lower_filesize));

        $width    = $lower_width;
        $height   = $lower_height;
        $filesize = $lower_filesize;

        $stepsize = 51200; // 50 KB

        if ( $source_width >= 4000 ) {
            $stepsize = 307200; // 300 KB
        }

        while (($width < $upper_width) && ($height < $upper_height)) {
            $width     += $width_factor;
            $height    += $height_factor;
            $filesize  += $vars['step'];
            $dimensions = $width . 'x' . $height;
            $seen       = array();

            do {
                if ($width < $upper_width) {
                    clearstatcache();
                    $temp = self::$tmpFilepath . $source_filename . '-temp.' . $source_extension;
                    exec( escapeshellcmd(implode(' ', array('convert', '-scale', $dimensions, '-density', '72', '-quality', '50', $vars['source'], $temp))) );
                    if ($source_extension === 'png') {
                        $png = $temp;
                        exec(escapeshellcmd(implode(' ', array('convert', '-colors', '64', $temp, $png))));
                        $temp = $png;
                    }

                    $temp_size      = getimagesize($temp);
                    $temp_width     = $temp_size[0];
                    $temp_height    = $temp_size[1];
                    $temp_filesize  = filesize($temp);
                    $filesize_delta = $temp_filesize - $filesize;
                }

                if ($filesize_delta > $stepsize) { // filesize diff must bigger than 50kb
                    if (empty($seen[$filesize_delta])) {
                        $adjustment            = $temp_filesize / $filesize;
                        $width                -= $width_factor;
                        $height               -= $height_factor;
                        $width_factor          = floor($width_factor / $adjustment);
                        $height_factor         = floor($height_factor / $adjustment);
                        $width                += $width_factor;
                        $height               += $height_factor;
                        $dimensions            = $width . 'x' . $height;
                        $seen[$filesize_delta] = true;
                    }
                    else if ($filesize_delta < $vars['step']) {
                        break;
                    }
                }
            } while ($filesize_delta > $stepsize);

            if ($width < $upper_width) {
                $width  = $temp_width;
                $height = $temp_height;

                $destination = self::$dest . $source_filename . '-' . $temp_width . 'x' . $temp_height . '.' . $source_extension;
                if (!is_file($destination)) {
                    rename($temp, $destination);
                }
                $files[] = [
                    'name'   => $source_filename . '-' . $temp_width . 'x' . $temp_height . '.' . $source_extension,
                    'width'  => $temp_width,
                    'height' => $temp_height,
                ];
            }
        }

        $destination = self::$dest . $source_filename . '-' . $source_width . 'x' . $source_height . '.' . $source_extension;
        if (!is_file($destination)) {
            copy($vars['source'], $destination);
        }
        $files[] = [
            'name'   => $source_filename . '-' . $source_width . 'x' . $source_height . '.' . $source_extension,
            'width'  => $source_width,
            'height' => $source_height,
        ];
        return self::updateImageList($files, $id, $imgId);
    }


    /**
     * update file list, smart image viewhelper will search the images in this table and generate a html picture element
     *
     * @param   array     $_files        generated responsive images
     * @param   int       $_id           content ID (tt_content.uid)
     * @param   int       $_imgId        image ID (sys_file_reference.uid)
     *
     * @return  int                      the number of affected rows
     */
    protected static function updateImageList(array $_files, int $_id, int $_imgId): int
    {
        $insert = array();
        for ($i=0; $i<count($_files); $i++) {
            $insert[] = ['file' => $_files[$i]['name'], 'cid' => $_id, 'img_id' => $_imgId, 'width' => $_files[$i]['width'], 'height' => $_files[$i]['height']];
        }

        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_b8motor_breakpoint_images')->bulkInsert(
            'tx_b8motor_breakpoint_images',
            $insert,
            [ 'file', 'cid', 'img_id', 'width', 'height' ]
        );
    }
}
