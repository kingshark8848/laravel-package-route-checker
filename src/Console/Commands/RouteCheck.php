<?php

namespace Kingshark\RouteChecker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Kingshark\RouteChecker\CheckResult;
use Kingshark\RouteChecker\RouteChecker;

class RouteCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route-checker:name-conflicts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List the routes name conflicts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $routeChecker = new RouteChecker();

        $checkResult = $routeChecker->checkConflicts();

        $sortedConflictPairs = $checkResult->getConflictPairs(false, true);

        $headers = ['Route', 'Conflict Route'];

        $body = [];

        foreach ($sortedConflictPairs as $pair){
            /** @var Route $route1 */
            $route1 = $pair[0][CheckResult::ROUTE];
            /** @var Route $route2 */
            $route2 = $pair[1][CheckResult::ROUTE];

            $route1Data = $this->getRouteColumnData($route1);
            $route2Data = $this->getRouteColumnData($route2);

            $body [] = [
                'route1' => $route1Data,
                'route2' => $route2Data,
            ];

        }

        $this->table($headers, $body);

    }

    /**
     * @param $route
     * @return string
     */
    public function getRouteColumnData(Route $route)
    {
        $data = 'Methods: '.implode('|', $route->methods()).' URI: '.$route->uri();

        return $data;
    }
}
