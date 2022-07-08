<?php
declare(strict_types=1);

namespace B8\B8motor\Service;

use B8\B8motor\Interfaces\SmartImageInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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

class SmartImageService implements SmartImageInterface
{
    protected $cid            = 0;
    protected $tablenames     = '';
    protected $path           = '';
    protected $images         = array();
    protected $isArtDirection = false;
    protected $config         = array();

    const ALLOWED_FILE_EXT = [
        'jpg',
        'jpeg'
    ];

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
        // $data = $this->templateVariableContainer->get('data');

        // $this->cid = empty($data['_LOCALIZED_UID']) ? (int)$data['uid'] : (int)$data['_LOCALIZED_UID']; // content id

        $this->cid        = $cid;
        $this->tablenames = $tablenames;
        $this->path       = $path;

        $this->withArtDirection();
        $this->getMedia();

        if (empty($this->images)) {
            // get images from original content, if current content is in another language
            $this->getParentId();
            $this->withArtDirection();
            $this->getMedia();
        }

        $this->getConfig();

        // TODO: if more than one image in a content, can decide with yaml setting (setting in typo3/config/b8motor/basic/smartImage.yaml) whether image has art-direction or only a image group

        if ($this->isArtDirection === true) {
            $this->rebuildImageArray();
            return $this->buildHtmlWithAD();
        } else {
            return $this->buildHtml();
        }
    }


    /**
     * Test art-direction image
     * set variable isArtDirection true or false
     *
     * @return  void
     */
    protected function withArtDirection(): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
        $count = $queryBuilder
            ->count('uid')
            ->from('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('image')),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($this->tablenames)),
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($this->cid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchColumn(0);

        if ($count > 1) {
            $this->isArtDirection = true;
        }
    }

    /**
     * get all images
     *
     * @return  void
     */
    protected function getMedia(): void
    {
        $this->images  = array();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');

        $constraints = [
            $queryBuilder->expr()->eq('sys_file_reference.fieldname', $queryBuilder->createNamedParameter('image')),
            $queryBuilder->expr()->eq('sys_file_reference.tablenames', $queryBuilder->createNamedParameter($this->tablenames)),
            $queryBuilder->expr()->eq('sys_file_reference.uid_foreign', $queryBuilder->createNamedParameter($this->cid, \PDO::PARAM_INT)),
        ];

        $originalImage = $queryBuilder
            ->select('sys_file_reference.*', 'sys_file_reference.uid AS suid', 'sys_file.*')
            ->from('sys_file_reference')
            ->join(
                'sys_file_reference',
                'sys_file',
                'sys_file',
                $queryBuilder->expr()->eq('sys_file_reference.uid_local', $queryBuilder->quoteIdentifier('sys_file.uid'))
            )
            ->where(...$constraints)
            ->execute();

        while ($image = $originalImage->fetch()) {
            // tx_b8motor_as_responsive:
            //      0 => responsive, pictures from folder "fileadmin/breakpoints"
            //      1 => not responsive, has only 1 picture
            if (!is_null($image['extension']) && in_array(strtolower($image['extension']), self::ALLOWED_FILE_EXT) && $image['tx_b8motor_as_responsive'] === 0) {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_b8motor_breakpoint_images');
                $fileInfos = $queryBuilder
                    ->select('uid', 'file', 'width', 'height')
                    ->from('tx_b8motor_breakpoint_images')
                    ->where(
                        $queryBuilder->expr()->eq('cid', $queryBuilder->createNamedParameter($this->cid, \PDO::PARAM_INT)),
                        $queryBuilder->expr()->eq('img_id', $queryBuilder->createNamedParameter($image['suid'], \PDO::PARAM_INT))
                    )
                    ->orderBy('width', 'ASC')
                    ->execute();

                while ($fileinfo = $fileInfos->fetch()) {
                    $image['file']   = $fileinfo['file'];
                    $image['width']  = $fileinfo['width'];
                    $image['height'] = $fileinfo['height'];

                    $this->images[] = $image;
                }

            } else {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_metadata');
                $fileInfos = $queryBuilder
                    ->select('*')
                    ->from('sys_file_metadata')
                    ->where(
                        $queryBuilder->expr()->eq('file', $queryBuilder->createNamedParameter($image['uid'], \PDO::PARAM_INT))
                    )
                    ->execute();

                while ($fileinfo = $fileInfos->fetch()) {
                    $image['width']  = $fileinfo['width'];
                    $image['height'] = $fileinfo['height'];

                    $this->images[] = $image;
                }
            }
        }
    }

    /**
     * group art direction images by image style (db field: 'tx_b8motor_component_style')
     *
     * @return  void
     */
    protected function rebuildImageArray(): void
    {
        $tmp = array();
        foreach ($this->images as $image) {
            $tmp[$image['tx_b8motor_component_style']][] = $image;
        }
        $this->images = $tmp;
    }

    /**
     * get original content id, if current content in another language
     * set varialbe cid
     *
     * @return  void
     */
    protected function getParentId(): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $this->cid = $queryBuilder
            ->select('t3_origuid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($this->cid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchColumn(0);
    }

    /**
     * get image setting from yaml file
     * set variable config
     *
     * @return  void
     */
    protected function getConfig(): void
    {
        $file = \TYPO3\CMS\Core\Core\Environment::getProjectPath() . '/config/b8motor/basic/smartImage.yaml';
        if (is_file($file)) {
            $this->config = GeneralUtility::makeInstance(YamlFileLoader::class)->load($file);

            // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->config);

        } else {
            throw new \Exception('Frontend: configration file "' . $file .'" is missing.');
        }
    }

    /**
     * render normal picture component (without art-direction)
     *
     * @return  string  html component picture
     */
    protected function buildHtml(): string
    {
        $html = '';
        if (is_array($this->images)) {

            if (count($this->images) >= 1) {
                $amount = count($this->images);

                $srcset   = $this->config['smartImageSettings'][$this->images[0]['tx_b8motor_component_type']]['data-srcset'] ? 'data-srcset' : ' srcset';
                $lazyload = !is_null($this->config['smartImageSettings'][$this->images[0]['tx_b8motor_component_type']]['lazyload']) ? $this->config['smartImageSettings'][$this->images[0]['tx_b8motor_component_type']]['lazyload'] : '';


                if (!empty($this->images[0]['link'])) { // has link?
                    $linkService = GeneralUtility::makeInstance(LinkService::class);
                    $link = $linkService->resolve($this->images[0]['link']);

                    $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
                    $link = $cObj->typolink_URL([
                        'parameter'                 => $link['pageuid'],
                        'linkAccessRestrictedPages' => 1,
                    ]);
                    $html .= '<a href="'.$link.'" class="teaser--image">';
                }


                if (!empty($this->images[0]['description'])) { // has description?
                    $html .= '<figure>';
                }


                if ($this->images[0]['tx_b8motor_as_responsive'] === 1) { // is not responsive image
                    if ($this->images[0]['extension'] === 'svg') {
                        $svg      = file_get_contents('fileadmin' . $this->images[0]['identifier']);
                        $position = strpos($svg, '<svg');

                        $html .= substr($svg, $position);
                    } else {
                        $html .= '<picture><img '.$lazyload.' alt="'.(!is_null($this->images[0]['alternative'])?$this->images[0]['alternative']:$this->images[0]['name']).'" title="'.$this->images[0]['title'].'" src="/fileadmin' . $this->images[0]['identifier'] . '"></picture>';
                    }
                } else {
                    if ($this->images[0]['extension'] === 'svg' && count($this->images) === 1) {
                        /* SVG output */
                        $svg      = file_get_contents('fileadmin' . $this->images[0]['identifier']);
                        $position = strpos($svg, '<svg');

                        $html .= substr($svg, $position);
                        /* /SVG output */
                    } else {
                        /* get image settings */
                        $conf = $this->config['smartImageSettings'][$this->images[0]['tx_b8motor_component_type']]['style'][$this->images[0]['tx_b8motor_component_style']];

                        if ($conf['max-width'] === -1) {
                            $conf['sizes'] .= $this->images[$amount-1]['width'].'px';
                        }
                        $maxTo = $conf['to'];
                        if ($conf['to'] === -1) {
                            $maxTo = $this->images[$amount-1]['width'] . 'px';
                        }
                        /* get /image settings */


                        /* picture element */
                        $html .= '<picture><img sizes="' . $conf['sizes'] . '" class="img-fluid'.($lazyload!==''? ' '.$lazyload : '').'" '.$srcset.'="';
                        $i     = 0;
                        for ($i; $i<$amount; $i++) {
                            if (!is_null($this->images[$i]['extension']) && !in_array(strtolower($this->images[$i]['extension']), self::ALLOWED_FILE_EXT)) {
                                $html .= '/fileadmin'.$this->images[$i]['identifier'].' '.$this->images[$i]['width'].'w';
                            } else {
                                $html .= '/fileadmin/breakpoints/'.($this->path!==''?$this->path.'/':'').$this->cid.'/'.$this->images[$i]['file'].' '.$this->images[$i]['width'].'w';
                            }
                            if ($i < $amount-1) {
                                $html .= ',';
                            }
                            if ($this->images[$i]['width'] >= $maxTo) {
                                break;
                            }
                        }
                        if (!is_null($this->images[$i]['extension']) && !in_array(strtolower($this->images[$i]['extension']), self::ALLOWED_FILE_EXT)) {
                            $html .= '" src="/fileadmin'.$this->images[$i]['identifier'].'" '.$lazyload.' alt="'.(!is_null($this->images[0]['alternative'])?$this->images[0]['alternative']:$this->images[0]['name']).'" title="'.$this->images[0]['title'].'"></picture>';
                        } else {
                            $html .= '" src="/fileadmin/breakpoints/'.($this->path!==''?$this->path.'/':'').$this->cid.'/'.$this->images[floor($amount/2)]['file'].'" alt="'.(!is_null($this->images[0]['alternative'])?$this->images[0]['alternative']:$this->images[0]['name']).'" title="'.$this->images[0]['title'].'" '.($lazyload===''? 'loading="lazy"' : '').'></picture>';
                        }
                        /* /picture element */
                    }
                }

                if (!empty($this->images[0]['description'])) { // has description?
                    $html .= '<figcaption>'.$this->images[0]['description'].'</figcaption></figure>';
                }

                if (!empty($this->images[0]['link'])) { // has link?
                    $html .= '</a>';
                }
            }
        }
        return $html;
    }




    /**
     * render picture component (within art-direction)
     *
     * @return  string  html component picture
     */
    protected function buildHtmlWithAD(): string
    {
        $html = '';

        reset($this->images);
        $first_key = key($this->images);

        if (is_array($this->images)) {

            $srcset   = $this->config['smartImageSettings'][$this->images[$first_key][0]['tx_b8motor_component_type']]['data-srcset'] ? 'data-srcset' : ' srcset';
            $lazyload = !is_null($this->config['smartImageSettings'][$this->images[$first_key][0]['tx_b8motor_component_type']]['lazyload']) ? $this->config['smartImageSettings'][$this->images[$first_key][0]['tx_b8motor_component_type']]['lazyload'] : 'loading="lazy"';

            $html .= '<picture>';

            $conf = $this->config['smartImageSettings'][$this->images[$first_key][0]['tx_b8motor_component_type']]['style'];

            if (is_array($conf)) {
                $amount = count($conf); // how many styles?

                $i = $amount - 1;
                for ($i; $i>=0; $i--) {
                    if (!empty($this->images[$i]) > 0) {
                        $imgAmount = count($this->images[$i]);

                        if (!$conf[$i]['last']) {
                            $html .= '<source ' . (!is_null($conf[$i]['media']) ? ' media="'.$conf[$i]['media'].'" ' : '') . 'sizes="' . $conf[$i]['sizes'] .'" '.$srcset.'="';

                            foreach ($this->images[$i] as $image) {
                                /* get image settings */
                                $maxTo = $conf[$i]['to'];
                                if ($conf[$i]['to'] === -1) {
                                    $maxTo = $this->images[$i][$imgAmount-1]['width'];
                                }
                                /* get /image settings */


                                if ($image['width'] <= $maxTo && $image['width'] >= $conf[$i]['from'] && $image['tx_b8motor_component_style'] === (string)$i) {

                                    $path = !is_null($image['extension']) && !in_array(strtolower($image['extension']), self::ALLOWED_FILE_EXT) ? '/fileadmin'.$image['identifier'] : '/fileadmin/breakpoints/'.($this->path!==''?$this->path.'/':'').$this->cid.'/'.$image['file'];

                                    $html .= $path.' '.$image['width'].'w, ';
                                }
                            }
                            $html = substr($html, -2) === ', ' ? substr_replace($html, '', -2) : $html;

                            $html .= '">';
                        } else {
                            if ($conf[$i]['max-width'] === -1) {
                                $conf[$i]['sizes'] = $this->images[$i][$imgAmount-1]['width'] . 'px';
                            }

                            $html .= '<img sizes="' . $conf[$i]['sizes'] .'" '.$srcset.'="';

                            $lastImageIdex = 0;

                            foreach ($this->images[$i] as $key=>$image) {
                                /* get image settings */
                                $maxTo = $conf[$i]['to'];
                                if ($conf[$i]['to'] === -1) {
                                    $maxTo = $this->images[$i][$imgAmount-1]['width'];
                                }
                                /* get /image settings */

                                if ($image['width'] <= $maxTo && $image['width'] >= $conf[$i]['from'] && $image['tx_b8motor_component_style'] === (string)$i) {

                                    $path = !is_null($image['extension']) && !in_array(strtolower($image['extension']), self::ALLOWED_FILE_EXT) ? '/fileadmin'.$image['identifier'] : '/fileadmin/breakpoints/'.($this->path!==''?$this->path.'/':'').$this->cid.'/'.$image['file'];

                                    $html .= $path.' '.$image['width'].'w, ';

                                    $lastImageIdex = $key;
                                }
                            }

                            $html = substr($html, -2) === ', ' ? substr_replace($html, '', -2) : $html;
                            $html .= '"';
                            $html .= ' alt="'.(!is_null($this->images[0][$lastImageIdex]['alternative'])?$this->images[0][$lastImageIdex]['alternative']:$this->images[0][$lastImageIdex]['name']).'" title="'.$this->images[0][$lastImageIdex]['title'].'"';

                            $path = !is_null($this->images[0][$lastImageIdex]['extension']) && !in_array(strtolower($this->images[0][$lastImageIdex]['extension']), self::ALLOWED_FILE_EXT) ? '/fileadmin'.$image[0]['identifier'] : '/fileadmin/breakpoints/'.($this->path!==''?$this->path.'/':'').$this->cid.'/'.$this->images[0][$lastImageIdex]['file'];
                            $html .= ' src="'.$path.'" '.$lazyload;
                            $html .= '>';

                        }
                    }
                }
            }

            $html .= '</picture>';
        }
        return $html;
    }
}