<?php

namespace Kregel\Warden\Http\Controllers;

use BadMethodCallException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException("Method [$method] does not exist.");
    }
    public function checkParams(Array $params){
        foreach($params as $p){
            $config = config('kregel.warden.models');
            $configKeys = array_keys($config);
//            dd($p, $config, $configKeys, !in_array($p, $config), !in_array($p, $configKeys), !in_array($p, $configKeys) && !in_array($p, $config));
            if(in_array($p, $configKeys) && in_array($p, $config)){
                throw new \Exception('You\'re attempting to use a value that isn\'t in the config! '. print_r($configKeys, true));
            }
        }
    }
}















