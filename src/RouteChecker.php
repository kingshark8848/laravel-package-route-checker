<?php
/**
 * Created by PhpStorm.
 * User: kingshark
 * Date: 14/04/18
 * Time: 8:53 AM
 */

namespace Kingshark\RouteChecker;

use Illuminate\Support\Facades\Route;

class RouteChecker
{
    const GROUP_ROUTE_ALL = 'all';
    const GROUP_ROUTE_INCLUDE_PARAM = 'include_param';

    /**
     * @var \Illuminate\Routing\Route[]
     */
    private $routes;

    private $routesMapUriParts;

    public function __construct()
    {
        $this->genRoutes();
    }

    public function genRoutes()
    {
        $this->routes = Route::getRoutes()->getRoutes();

        $this->genRoutePartsMapping();

        return $this;
    }

    public function genRoutePartsMapping()
    {
        /** @var array $routes */
        $routes = $this->routes;

        $routesMapUriParts = [];

        /** @var \Illuminate\Routing\Route $route */
        foreach ($routes as $index=>$route){

            $uri = $route->uri();

            $partsOfUri = $this->getPartsOfUri($uri);
            $routesMapUriParts[$index] = $partsOfUri;

        }

        $this->routesMapUriParts = $routesMapUriParts;

        return $this;
    }

    /**
     * @param string $uri
     * @return array
     */
    public function getPartsOfUri(string $uri) : array
    {
        $data = explode('/', $uri);

        return $data;
    }

    /**
     * @param $uri
     * @return bool
     */
    public function hasRouteParam($uri) : bool
    {
        $result = preg_match("/\{.+?\}/i", $uri);

        return $result===1;

    }

    /**
     * @param $uriPart
     * @return bool
     */
    public function isUriPartParam($uriPart) : bool
    {
        $result = preg_match("/^\{.+?\}$/i", $uriPart);

        return $result===1;
    }

    /**
     * @param $route1Index
     * @param $route2Index
     * @return bool
     * @throws \Exception
     */
    public function isTwoRoutesConflict($route1Index, $route2Index) : bool
    {
        if ($route1Index === $route2Index){
            return false;
        }

        /* parts count */
        $route1UriParts = $this->routesMapUriParts[$route1Index];
        $route2UriParts = $this->routesMapUriParts[$route2Index];

        if (count($route1UriParts) !== count($route2UriParts)){
            return false;
        }

        /* methods */
        $route1Methods = $this->routes[$route1Index]->methods();
        $route2Methods = $this->routes[$route2Index]->methods();

        $sameMethods = array_intersect($route1Methods, $route2Methods);

        if (empty($sameMethods)){
            return false;
        }

        /* parts pattern match */
        $child = null; /* 1|2 */

        foreach ($route1UriParts as $index=>$route1Part){

            $route2Part = $route2UriParts[$index];

            $route1Part = $this->isUriPartParam($route1Part)?'.':$route1Part;
            $route2Part = $this->isUriPartParam($route2Part)?'.':$route2Part;

            if ($route1Part === $route2Part){
                continue;
            }

            if ($child===null){

                if ($route1Part==='.'){
                    $child = 2;
                }
                elseif ($route2Part==='.'){
                    $child = 1;
                }
                else{
                    return false;
                }

            }
            else {

                if ($child===1 && $route2Part!=='.'){
                    return false;
                }
                elseif ($child===2 && $route1Part!=='.'){
                    return false;
                }
            }


        }

        if ($child === null){
            return true;
        }
        elseif ($child === 1){
            return $route1Index>$route2Index;
        }
        elseif ($child === 2){
            return $route2Index>$route1Index;
        }
        else{
            throw new \Exception('unknown child value');
        }

    }

    /**
     * @return CheckResult
     * @throws /Exception
     */
    public function checkConflicts(): CheckResult
    {
        $checkResult = new CheckResult($this->routes);

        /** @var \Illuminate\Routing\Route $route */
        foreach ($this->routes as $index1=>$route){

            /* skip non-param routes */
            if (!$this->hasRouteParam($route->uri())){
                continue;
            }

            for ($index2=$index1+1; $index2<count($this->routes); $index2++ ){

                $isConflict = $this->isTwoRoutesConflict($index1, $index2);

                if ($isConflict){
                    $checkResult->addConflictRouteIndexPair($index1, $index2);
                }
            }

        }

        return $checkResult;
    }

}