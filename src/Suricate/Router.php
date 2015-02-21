<?php
namespace Suricate;

/**
// TODO : handle closure
**/
class Router extends Service
{
    private $requestUri;
    private $routes;
    private $response;
    private $appMiddlewares = array(
        '\Suricate\Middleware\CheckMaintenance',
        );



    public function __construct()
    {
        $this->routes   = array();
        $this->response = Suricate::Response();
        $this->parseRequest();
    }

    public function configure($parameters = array())
    {

        foreach ($parameters as $routeName => $routeData) {
            if (isset($routeData['isRest']) && $routeData['isRest']) {
                $this->buildRestRoutes($routeName, $routeData);
            } else {
                $this->buildRoute($routeName, $routeData);
            }
        }
    }

    private function buildRoute($routeName, $routeData) {
        if (isset($routeData['target'])) {
            $routeTarget = explode('::', $routeData['target']);
        } else {
            $routeTarget = null;
        }
        $routeMethod    = isset($routeData['method']) ? $routeData['method'] : 'any';
        $parameters     = isset($routeData['parameters']) ? $routeData['parameters'] : array();
        

        if (isset($routeData['middleware'])) {
            $middleware = (array)$routeData['middleware'];
        } else {
            $middleware = array(); 
        }

        $this->addRoute(
            $routeName,
            $routeMethod,
            $routeData['path'],
            $routeTarget,
            $parameters,
            $middleware
        );
    }

    private function buildRestRoutes($routeBaseName, $routeBaseData)
    {
        $resources = array(
            'index'     => array('method' => 'GET', 'append' => ''),
            'create'    => array('method' => 'GET', 'append' => '/create'),
            'store'     => array('method' => 'POST', 'append' => ''),
            'show'      => array('method' => 'GET', 'append' => '/:id'),
            'edit'      => array('method' => 'GET', 'append' => '/:id/edit'),
            'update'    => array('method' => 'PUT', 'append' => '/:id'),
            'destroy'   => array('method' => 'DELETE', 'append' => '/:id'),
            );

        foreach ($resources as $name => $definition) {
            $routeName = $routeBaseName . '.' . $name;
            $routeData = $routeBaseData;
            $routeData['method'] = $definition['method'];
            $routeData['path'] .= $definition['append'];
            $routeData['target'] .= '::' . $name;
            $routeData['parameters'] = array('id' => '[^/]*');

            $this->buildRoute($routeName, $routeData);
        }
    }

    private function parseRequest()
    {
        $this->requestUri = Suricate::Request()->getRequestUri();
        $this->response->setRequestUri($this->requestUri);
    }

    public function addRoute($routeName, $routeMethod, $routePath, $routeTarget, $parametersDefinitions, $middleware = null)
    {
        $this->routes[$routeName] = new Route(
            $routeName,
            $routeMethod,
            $routePath,
            Suricate::Request(),
            $routeTarget,
            $parametersDefinitions,
            $middleware
        );
    }

    public function addMiddleware($middleware)
    {
        array_unshift($this->appMiddlewares, $middleware);

        return $this;
    }

    public function getMiddlewares()
    {
        return $this->appMiddlewares;
    }

    public function getResponse()
    {
        return $this->response;
    }
    /**
     * Loop through each defined routes, to find good one
     * @return null
     */
    public function doRouting()
    {
        $hasRoute = false;
        foreach ($this->routes as $route) {
            if ($route->isMatched) {

                Suricate::Logger()->debug('Route "' . $route->getPath() . '" matched, target: ' . json_encode($route->target));
                $hasRoute = $route->dispatch($this->response, $this->appMiddlewares);
            }
        }

        // No route matched
        if (!$hasRoute) {
            Suricate::Logger()->debug('No route found');
            app()->abort('404');
        }

        $this->response->write();
    }
}
