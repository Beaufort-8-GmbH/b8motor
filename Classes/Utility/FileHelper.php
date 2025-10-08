<?php
declare(strict_types = 1);

namespace B8\B8motor\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

/***************************************************************
*  Copyright notice
*
*  (c) 2018 - 2023 Feng Lu <lu@beaufort8.de>
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

class FileHelper
{
    /**
     * copy path
     *
     * @param   string  $_dirsrc  source
     * @param   string  $_dirto   distination
     *
     * @return  void
     */
    public static function copydir(string $_dirsrc, string $_dirto): void
    {
        if (file_exists($_dirto)) {
            if (!is_dir($_dirto)) {
                throw new \Exception('Can\'t read directory "' . $_dirto . '"');
            }
        } else {
            mkdir($_dirto, 0775, true);
        }

        $dir = opendir($_dirsrc);

        while ($filename = readdir($dir)) {
            if ($filename !== '.' && $filename !== '..') {
                $srcfile = $_dirsrc . '/'. $filename;
                $tofile  = $_dirto . '/' . $filename;

                if (is_dir($srcfile)) {
                    copydir($srcfile, $tofile);
                } else {
                    copy($srcfile, $tofile);
                }
            }
        }
    }

    /**
     * convert size to readable format
     *
     * @param   int     $_bytes  size as integer
     *
     * @return  string           size in readable format
     */
    public static function convertSize(int $_bytes): string
    {
        if ($_bytes >= 1024*1024*1024) {
            return number_format($_bytes / 1073741824, 0) . ' GB';
        } elseif ($_bytes >= 1024*1024) {
            return number_format($_bytes / 1048576, 0) . ' MB';
        } elseif ($_bytes >= 1024) {
            return number_format($_bytes / 1024, 0) . ' KB';
        } elseif ($_bytes > 1) {
            return $_bytes . ' B';
        } elseif ($_bytes == 1) {
            return '1 B';
        } else {
            return '0 B';
        }
    }

    /**
     * get all subpage ids from parent pages
     *
     * Note:
     * - QueryGenerator::getTreeList() is deprecated/removed in TYPO3 v12.
     * - Use PageRepository->getDescendantPageIdsRecursive() to fetch subpage IDs (excluding the start page),
     *   then convert the resulting int[] to a comma-separated string to keep the original return type.
     *
     * @param   string  $_parentIds  parent page IDs (comma-separated)
     * @param   int     $_deep       depth
     *
     * @return  string               subpage ids (comma-separated)
     */
    public static function getSubPids(string $_parentIds, int $_deep = 1000): string
    {
        $_parentIds = explode(',', $_parentIds);

        $pids = '';
        // Replace deprecated QueryGenerator->getTreeList() with PageRepository recursion
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);

        foreach ($_parentIds as $id) {
            $id = (int)$id;
            if ($id <= 0) {
                continue;
            }
            // Get descendant page IDs (excluding the start page) and convert to CSV
            $descendants = $pageRepository->getDescendantPageIdsRecursive($id, $_deep);
            if (!empty($descendants)) {
                $pids .= implode(',', $descendants) . ',';
            }
        }
        if (substr($pids, -1) === ',') {
            $pids = substr($pids, 0, strlen($pids)-1);
        }
        return $pids;
    }


    /**
     * remove file
     *
     * @param   string  $_file  file to remove
     *
     * @return  bool            removed or not
     */
    public static function removeFile(string $_file): bool
    {
        if ( is_file($_file) ) {
            try {
                return unlink($_file);
            }
            catch (\Exception $e) {
                throw new \Exception('Can\'t delete file "' . $_file . '"');
            }
        }
        return true;
    }
}
