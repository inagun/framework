<?php
namespace Suricate\Middleware;

class CheckMaintenance implements \Suricate\Interfaces\IMiddleware
{
    public function call(&$response)
    {
        if (app()->inMaintenance()) {
            app()->abort(503, 'Maintenance in progress');
        }
    }
}
