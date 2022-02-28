<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('/api/v1', function(RouteCollectorProxy $group){
    $group->get('/albums',"App\Controllers\AlbumsController:getAll");
});