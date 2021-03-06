<?php
declare(strict_types = 1);

namespace B8\B8motor\Scheduler;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Mail\MailMessage;

/***************************************************************
*  Copyright notice
*
*  (c) 2018-2020 Feng Lu <lu@beaufort8.de>
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

/**
 * This class provides a scheduler task to check image contents and related files
 *
 * @author Feng Lu <lu@beaufort8.de>
 */
class SmartImageHealthCheck extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{
    const DEFAULT_FILE_PATH = '/typo3temp/b8motor';
    const IMAGE_FILE_PATH   = '/fileadmin/breakpoints/';
    const MAIL_TEMPLATE     = 'EXT:b8motor/Resources/Private/Partials/MailTemplates/SmartImage_Health_Check_Report.html';

    protected $images = array();

    public function __construct()
    {
        parent::__construct();

        if (!is_dir(Environment::getPublicPath() . self::DEFAULT_FILE_PATH)) {
            @mkdir(Environment::getPublicPath() . self::DEFAULT_FILE_PATH, 0775, true);
        }
    }

    /**
     * @return boolean
     */
    public function execute(): bool
    {
        $this->checkFiles();

        $from = MailUtility::getSystemFrom();
        $to   = array('lu@beaufort8.de');

        // $from = $to = MailUtility::getSystemFrom();
        // $cc = array(
        //   "lu@beaufort8.de" => "Feng Lu",
        // );

        if (!$this->sendMessage($this->writeLog(), $from, $to)) {
            throw new \Exception('SmartImage HealthCheck: can\'t send mail to ' . $to);
        }

        return true;
    }

    protected function checkFiles(): void
    {
        $this->images = array();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_b8motor_breakpoint_images');
        $images = $queryBuilder
            ->select('tx_b8motor_breakpoint_images.*', 'tt_content.pid', 'tt_content.deleted')
            ->from('tx_b8motor_breakpoint_images')
            ->join(
                'tx_b8motor_breakpoint_images',
                'tt_content',
                'tt_content',
                $queryBuilder->expr()->eq('tt_content.uid', $queryBuilder->quoteIdentifier('tx_b8motor_breakpoint_images.cid')),
            )
            ->where(
                $queryBuilder->expr()->eq('tx_b8motor_breakpoint_images.deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
            )
            ->execute();

        while ($image = $images->fetch()) {
            $this->images[] = $image;
        }

    }

    protected function writeLog(): string
    {
        $mailContent = '';

        if (!empty($this->images)) {
            if (!is_dir(Environment::getPublicPath() . self::DEFAULT_FILE_PATH)) {
                @mkdir(Environment::getPublicPath() . self::DEFAULT_FILE_PATH, 0775, true);
            }

            $filename    = Environment::getPublicPath() . self::DEFAULT_FILE_PATH . '/smartimage_heathcheck_filecheck.log';
            $file        = fopen($filename, 'w+');
            $cid         = 0;
            $str         = '';

            foreach ($this->images as $image) {
                if (!is_file(Environment::getPublicPath() . self::IMAGE_FILE_PATH . $image['cid'] . '/' . $image['file'])) {
                    if ($cid !== $image['cid']) {
                        $str = chr(10) . 'Page ID: ' . $image['pid'] . ', Content ID: ' . $image['cid'] . ($image['deleted']==='1'?' (CONTENT DELETED)':'') . chr(10) . 'Image path: ' . $image['cid'] . '/' . $image['file'] . ' (Index: ' . $image['uid'] . ')' . chr(10);
                        $cid = $image['cid'];
                    } else {
                        $str = 'Image path: ' . $image['cid'] . '/' . $image['file'] . ' (Index: ' . $image['uid'] . ')' . chr(10);
                    }
                    fwrite($file, $str);
                    $mailContent .= $str;
                }
            }

            fclose($file);
        }

        return $mailContent;
    }

    protected function sendMessage(string $mailContent, $from, $to): bool
    {
        if (!empty($this->images)) {
            if ($mailContent === '') {
                $subject     = 'HURRA! Smart Image hat keinen Fehler entdeckt (' .date('d-m-Y H:i:s', time()). ')';
                $mailContent = '';
                $color       = 'green';
            }
            else {
                $subject = 'Smart Image hat Fehler entdeckt (' .date('d-m-Y H:i:s', time()). ')';
                $color   = 'red';
            }

            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $message       = $objectManager->get(StandaloneView::class);

            $message->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(self::MAIL_TEMPLATE));

            $message->assign('messageType',   'send-mail-to-admin');
            $message->assign('email-subject',  $subject);
            $message->assign('email-bodytext', $mailContent);
            $message->assign('color',          $color);
            $message->assign('email-eom',      '');

            $mailBody = $message->render();

            $mail = GeneralUtility::makeInstance(MailMessage::class);
            $mail->setFrom($from)->setTo($to)->setCc($cc);
            $mail->setSubject('Smart Image Health Check Report')->setBody($mailBody, 'text/html');
            $mail->send();

            return $mail->isSent();
        }

        return false;
    }
}


