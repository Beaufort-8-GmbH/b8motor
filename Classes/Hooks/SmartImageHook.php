<?php
declare(strict_types = 1);

namespace B8\B8motor\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use B8\B8motor\Utility\ImageHelper;
use B8\B8motor\Utility\FileHelper;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;


/***************************************************************
*  Copyright notice
*
*  (c) 2018 - 2025 Feng Lu <lu@beaufort8.de>
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

class SmartImageHook
{
    const STATUS_UPDATE    = 'update';
    const STATUS_NEW       = 'new';
    const ALLOWED_FILE_EXT = [
        'jpg',
        'jpeg'
    ];
    const WIDTH_LIMIT      = 200;
    const IMAGE_AMOUNT     = 5;
    const ORIGINAL_PATH    = '/fileadmin';
    const TEMP_PATH        = '/fileadmin/breakpoints/_tmp_/';
    const IMAGE_FILE_PATH  = '/fileadmin/breakpoints/';

    protected $allowed     = array();

    public function __construct()
    {
        $file = \TYPO3\CMS\Core\Core\Environment::getProjectPath() . '/config/b8motor/basic/smartImage.yaml';
        if (is_file($file)) {
            $settings = GeneralUtility::makeInstance(YamlFileLoader::class)->load($file);

            if (count($settings) > 0 && count($settings['smartImageSettings']) > 0) {
                foreach ($settings['smartImageSettings'] as $setting) {
                    if (!is_null($setting['plugin'])) {
                        $this->allowed[$setting['plugin']] = $setting['path'];
                    }
                }
            }

            // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->allowed);

        } else {
            throw new \Exception('Frontend: configration file "' . $file .'" is missing.');
        }
    }

    /**
     * Hook, after database saved.
     * @param   string                                    $status      process status, for example: "update"
     * @param   string                                    $table       database table
     * @param   string                                    $id          current page id
     * @param   array                                     $fieldArray  database field, that should be changed
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $pObj        DataHandler Object
     *
     * @return  void
     */
    public function processDatamap_afterDatabaseOperations(string $status, string $table, string $id, array $fieldArray, \TYPO3\CMS\Core\DataHandling\DataHandler $pObj): void
    {
        $res = array();
        if ($status === self::STATUS_NEW) {
            $id = $pObj->substNEWwithIDs[$id];
        } elseif ($status === self::STATUS_UPDATE) {
            $id = $id;
        }

        // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->allowed, $table, $fieldArray);
        // exit;
        // only if image created or updated
        if (
            !empty($fieldArray['image'])
                &&
            ($table === 'tt_content' || $table === 'pages' || array_key_exists($table, $this->allowed))) {

            $newImageId = 0;
            if ($status === self::STATUS_NEW && !empty($pObj->checkValue_currentRecord['image'])) {
                $newImageId = $pObj->checkValue_currentRecord['image'];
            } else {
                $newImageId = $pObj->newRelatedIDs['sys_file_reference'][0];
            }

            if (!empty($newImageId)) {
                if (stristr((string)$newImageId, ',')) { // copy page or component
                    $newImageId = explode(',', $newImageId);
                    foreach ($newImageId as $imageId) {
                        $this->doResponsive((int)$id, (int)$imageId, $table, !empty($this->allowed[$table]) ? $this->allowed[$table] . '/' : '');
                    }
                } else {
                    $this->doResponsive((int)$id, (int)$newImageId, $table, !empty($this->allowed[$table]) ? $this->allowed[$table] . '/' : '');
                }
            }
        }
    }

    /**
     * hook that is called when an element shall get deleted
     *
     * @param   string                                    $table             table of the record
     * @param   int                                       $id                ID of the record
     * @param   array                                     $recordToDelete    The accordant database record
     * @param   bool                                      $recordWasDeleted  can be set so that other hooks or
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  &$pObj             reference to the main DataHandler object
     *
     * @return  void
     */
    public function processCmdmap_deleteAction(string $table, int $id, array $recordToDelete, bool $recordWasDeleted=NULL, \TYPO3\CMS\Core\DataHandling\DataHandler &$pObj): void
    {
        if (isset($recordToDelete['tablenames'])) {
            $pluginImagePath = !empty($this->allowed[$recordToDelete['tablenames']]) ? $this->allowed[$recordToDelete['tablenames']] . '/' : '';
            // var_dump($pluginImagePath);

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_b8motor_breakpoint_images');
            $queryBuilder
                ->update('tx_b8motor_breakpoint_images')
                ->where(
                    $queryBuilder->expr()->eq('img_id', $queryBuilder->createNamedParameter($id, \Doctrine\DBAL\ParameterType::INTEGER)),
                    $queryBuilder->expr()->eq('cid', $queryBuilder->createNamedParameter((int)$recordToDelete['uid_foreign'], \Doctrine\DBAL\ParameterType::INTEGER))
                )
                ->set('deleted', 1)
                ->executeStatement();

            // get file names
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_b8motor_breakpoint_images');
            $statement = $queryBuilder
                ->select('file')
                ->from('tx_b8motor_breakpoint_images')
                ->where(
                    $queryBuilder->expr()->eq('img_id', $queryBuilder->createNamedParameter($id, \Doctrine\DBAL\ParameterType::INTEGER)),
                    $queryBuilder->expr()->eq('cid', $queryBuilder->createNamedParameter((int)$recordToDelete['uid_foreign'], \Doctrine\DBAL\ParameterType::INTEGER))
                )
                ->executeQuery();

            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $messageQueue        = $flashMessageService->getMessageQueueByIdentifier();

            while ($r = $statement->fetchAssociative()) {
                $title = 'Smart Image';

                $file  = Environment::getPublicPath() . self::IMAGE_FILE_PATH . $pluginImagePath . $recordToDelete['uid_foreign'] . '/' . $r['file'];

                if (!FileHelper::removeFile($file)) {
                    $severity = ContextualFeedbackSeverity::WARNING;
                    $prompt   = $file . ' can\'t remove.';
                } else {
                    $severity = ContextualFeedbackSeverity::OK;
                    $prompt   = $file . ' removed.';
                }
                $message = GeneralUtility::makeInstance(FlashMessage::class, $prompt, $title, $severity, false); // NOTICE, INFO, OK, WARNING, ERROR

                $messageQueue->enqueue($message);
            }
        }
    }

    /**
     * resize and save the responsive images
     *
     * @param   int     $id          ID of the record
     * @param   int     $newImageId  image ID
     * @param   string  $table       table of the record
     * @param   string  $path        special path of plugin image
     *
     * @return  void
     */
    private function doResponsive(int $id, int $newImageId, string $table, string $path): void
    {
        $file = $this->geImageFileInfos($newImageId, $table);

        if (!is_null($file['extension']) && in_array(strtolower($file['extension']), self::ALLOWED_FILE_EXT)) {
            try {
                ImageHelper::setDest(Environment::getPublicPath() . self::IMAGE_FILE_PATH . $path . $id . '/');
                ImageHelper::setOrgFilepath(Environment::getPublicPath() . self::ORIGINAL_PATH);
                ImageHelper::setTmpFilepath(Environment::getPublicPath() . self::TEMP_PATH);

                ImageHelper::setLimitWidth(self::WIDTH_LIMIT);
                ImageHelper::setBreakpointsAmount(self::IMAGE_AMOUNT);

                $rows = ImageHelper::makeBreakpoints($file, $id, $newImageId);


                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $messageQueue        = $flashMessageService->getMessageQueueByIdentifier();

                $title = 'Smart Image';

                if ($rows > 0) {
                    $severity = ContextualFeedbackSeverity::OK;
                    $prompt   = 'Images saved.';
                } else {
                    $severity = ContextualFeedbackSeverity::ERROR;
                    $prompt   = 'Images would not saved.';
                }
                $message  = GeneralUtility::makeInstance(FlashMessage::class, $prompt, $title, $severity, false); // NOTICE, INFO, OK, WARNING, ERROR

                $messageQueue->enqueue($message);

            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }


    /**
     * get image infos (identifier, name, extension, size, width, ...)
     *
     * @param   int     $id     image ID
     * @param   string  $table  table of the record
     *
     * @return  array           image file infos
     */
    private function geImageFileInfos(int $id, string $table): array
    {
        $info = array();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $constraints = [
            $queryBuilder->expr()->eq('sys_file_reference.tablenames', $queryBuilder->createNamedParameter($table)),
            $queryBuilder->expr()->eq('sys_file_reference.uid', $queryBuilder->createNamedParameter($id, \Doctrine\DBAL\ParameterType::INTEGER)),
            $queryBuilder->expr()->eq('sys_file_reference.deleted', $queryBuilder->createNamedParameter(0, \Doctrine\DBAL\ParameterType::INTEGER)),
            $queryBuilder->expr()->eq('sys_file_reference.hidden', $queryBuilder->createNamedParameter(0, \Doctrine\DBAL\ParameterType::INTEGER)),
        ];

        $info = $queryBuilder
            ->select('sys_file_reference.*', 'sys_file.uid', 'sys_file.identifier', 'sys_file.name', 'sys_file.extension', 'sys_file.size', 'sys_file_metadata.width', 'sys_file_metadata.height')
            ->from('sys_file_reference')
            ->join(
                'sys_file_reference',
                'sys_file',
                'sys_file',
                $queryBuilder->expr()->eq('sys_file.uid', $queryBuilder->quoteIdentifier('sys_file_reference.uid_local'))
            )
            ->join(
                'sys_file',
                'sys_file_metadata',
                'sys_file_metadata',
                $queryBuilder->expr()->eq('sys_file_metadata.file', $queryBuilder->quoteIdentifier('sys_file.uid'))
            )
            ->join(
                'sys_file_reference',
                $table,
                $table,
                $queryBuilder->expr()->eq($table . '.uid', $queryBuilder->quoteIdentifier('sys_file_reference.uid_foreign'))
            )
            ->where(...$constraints)
            ->executeQuery()
            ->fetchAssociative();

        // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($queryBuilder->getParameters(), $queryBuilder->getSql());


        return is_bool($info) ? array() : $info;

    }
}
