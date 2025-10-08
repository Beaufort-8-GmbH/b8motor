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

class AjaxMiddleware implements MiddlewareInterface
{
    private array $actions = [];
    private array $ext = [];
    private array $vendor = [];
    private array $paramNames = [];

    public function __construct()
    {
        // Note: Exceptions may also be triggered during the construction phase; try to avoid throwing exceptions at construction time and defer the check instead.
        $file = Environment::getProjectPath() . '/config/b8motor/basic/ajaxcalls.yaml';
        if (is_file($file)) {
            $settings = GeneralUtility::makeInstance(YamlFileLoader::class)->load($file);
            if (!empty($settings['MethodesMapping'])) {
                $map = $settings['MethodesMapping'];
                $this->actions = $map['action'] ?? [];
                $this->ext = $map['extension'] ?? [];
                $this->vendor = $map['vendor'] ?? [];
                $this->paramNames = $map['name'] ?? [];
            }
        } else {
            throw new \Exception('Frontend: configuration file "' . $file .'" is missing.');
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Skip Install tool / upgrade requests, determine by path or script name
        $uri = $request->getUri();
        $path = $uri->getPath();

        // Skip this middleware logic if the URL contains /typo3/install.php or the request script is install.php
        if (str_ends_with($path, '/typo3/install.php') || str_contains($path, '/typo3/install.php')) {
            return $handler->handle($request);
        }

        // Also, check for the presence of the install[controller] parameter to determine if it's an Install Tool request
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['install']) && is_array($queryParams['install'])) {
            // This is likely an Install Tool request
            return $handler->handle($request);
        }

        // Continue with the original logic
        $action    = $this->getParamFromRequest($request, $this->paramNames['action'] ?? '');
        $extension = $this->getParamFromRequest($request, $this->paramNames['ext'] ?? '');
        $vendor    = $this->getParamFromRequest($request, $this->paramNames['vendor'] ?? '');

        if (empty($action) || empty($extension) || empty($vendor)) {
            return $handler->handle($request);
        }

        $param = $this->getParamFromRequest($request, $this->paramNames['param'] ?? '') ?? '';

        if (isset($this->vendor[$vendor], $this->ext[$extension], $this->actions[$action])) {
            // Instantiate service and controller via GeneralUtility (ObjectManager removed in TYPO3 v12)
            $ajaxService = GeneralUtility::makeInstance(AjaxService::class);

            $controllerClass = ucfirst($this->vendor[$vendor]) . '\\' .
                               ucfirst($this->ext[$extension]) . '\\Controller\\' .
                               ucfirst($this->actions[$action]) . 'AjaxController';

            try {
                $ajaxController = GeneralUtility::makeInstance($controllerClass, $ajaxService, $param);
                $data = $ajaxController->getData();
                return new HtmlResponse(json_encode($data, JSON_UNESCAPED_UNICODE));
            } catch (\Throwable $e) {
                // If the controller does not exist or an exception occurs, fall back to the next handler instead of interrupting
                return $handler->handle($request);
            }
        }

        return $handler->handle($request);
    }

    protected function getParamFromRequest(ServerRequestInterface $request, string $name): ?string
    {
        if ($name === '') {
            return null;
        }
        $body = $request->getParsedBody();
        if (is_array($body) && array_key_exists($name, $body)) {
            return (string)$body[$name];
        }
        $query = $request->getQueryParams();
        return array_key_exists($name, $query) ? (string)$query[$name] : null;
    }
}