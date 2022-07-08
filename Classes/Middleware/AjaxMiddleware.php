<?php
declare(strict_types=1);

namespace B8\B8motor\Middleware;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use B8\B8motor\Service\AjaxService;

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

/**
 * generic ajax middleware
 */
class AjaxMiddleware implements MiddlewareInterface
{
    private $actions = array();
    private $ext = array();
    private $vendor = array();
    private $paramNames = array();


    public function __construct()
    {
        $file = Environment::getProjectPath() . '/config/b8motor/basic/ajaxcalls.yaml';
        if (is_file($file)) {
            $settings = GeneralUtility::makeInstance(YamlFileLoader::class)->load($file);

            if (count($settings) > 0 && count($settings['MethodesMapping']) > 0) {
                $this->actions    = $settings['MethodesMapping']['action'];
                $this->ext        = $settings['MethodesMapping']['extension'];
                $this->vendor     = $settings['MethodesMapping']['vendor'];
                $this->paramNames = $settings['MethodesMapping']['name'];
            }

        } else {
            throw new \Exception('Frontend: configration file "' . $file .'" is missing.');
        }
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $action    = $this->getParamFromRequest($request, $this->paramNames['action']);
        $extension = $this->getParamFromRequest($request, $this->paramNames['ext']);
        $vendor    = $this->getParamFromRequest($request, $this->paramNames['vendor']);

        if (empty($action) || empty($extension) || empty($vendor)) {
            return $handler->handle($request);
        } else {
            $param = empty($this->getParamFromRequest($request, $this->paramNames['param'])) ? '' : $this->getParamFromRequest($request, $this->paramNames['param']);

            if (!empty($this->vendor[$vendor]) && !empty($this->ext[$extension]) && !empty($this->actions[$action])) {
                $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);

                $ajaxService    = $objectManager->get(AjaxService::class);
                $ajaxController = $objectManager->get(ucfirst($this->vendor[$vendor]).'\\'.ucfirst($this->ext[$extension]).'\\Controller\\' . ucfirst($this->actions[$action]) . 'AjaxController', $ajaxService, $param);

                $data = $ajaxController->getData();

                return new HtmlResponse(json_encode($data, JSON_UNESCAPED_UNICODE));
            }

            return $handler->handle($request);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $name
     * @return mixed
     */
    protected function getParamFromRequest(ServerRequestInterface $request, string $name): ?string
    {
        return $request->getParsedBody()[$name] ?? $request->getQueryParams()[$name] ?? null;
    }
}