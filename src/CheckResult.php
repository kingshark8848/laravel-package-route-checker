<?php
/**
 * Created by PhpStorm.
 * User: kingshark
 * Date: 14/04/18
 * Time: 8:54 AM
 */

namespace Kingshark\RouteChecker;

use Illuminate\Routing\Route;

class CheckResult
{
    const ROUTE_INDEX = 'route_index';
    const ROUTE= 'route';

    /**
     * @var array
     */
    private $conflictPairs = [];

    /**
     * @var Route[]
     */
    private $routesList;

    public function __construct(array $routesList)
    {

        $this->routesList = $routesList;
    }

    /**
     * @param array $conflictPairs
     * @return CheckResult
     */
    public function setConflictPairs(array $conflictPairs): CheckResult
    {
        $this->conflictPairs = $conflictPairs;
        return $this;
    }

    /**
     * @param bool $onlyIndex
     * @param bool $isSort
     * @return array
     */
    public function getConflictPairs($onlyIndex=true, $isSort=false): array
    {
        $conflictPairs = $this->conflictPairs;

        if ($isSort){
            $conflictPairs = collect($conflictPairs)
                ->sortBy(0)
                ->sortBy(1)
                ->toArray();
        }

        if ($onlyIndex){
            $result = $conflictPairs;
        }
        else{
            $result = [];

            foreach ($this->conflictPairs as $pair){
                $result [] = $this->getPairOfRoutes($pair);
            }
        }

        return $result;

    }

    public function getPairOfRoutes(array $indexPair)
    {
        $route1 = $this->getRouteByIndex($indexPair[0]);
        $route2 = $this->getRouteByIndex($indexPair[1]);

        $result = [
            [self::ROUTE_INDEX=>$indexPair[0], self::ROUTE=>$route1],
            [self::ROUTE_INDEX=>$indexPair[1], self::ROUTE=>$route2],
        ];

        return $result;
    }

    public function addConflictRouteIndexPair($route1Index, $route2Index): CheckResult
    {
        $this->conflictPairs [] = [$route1Index, $route2Index];

        return $this;
    }

    public function genBasicReport()
    {
        $reportData = [];

        $sortedConflictPairs = $this->getConflictPairs(false,true);

        foreach ($sortedConflictPairs as $pair){

            $reportData [] = [
                $this->getBasicInfoOfRoute($pair[0][self::ROUTE]),
                $this->getBasicInfoOfRoute($pair[1][self::ROUTE]),
            ];
        }

        return $reportData;
    }

    /**
     * @param \Illuminate\Routing\Route $route
     * @return array
     */
    public function getBasicInfoOfRoute(Route $route)
    {
        return [
            'methods' => $route->methods(),
            'uri' => $route->uri(),
        ];
    }

    public function getRouteByIndex(int $routeIndex)
    {
        return data_get($this->routesList, $routeIndex);
    }
}