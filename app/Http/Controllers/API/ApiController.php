<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;

class ApiController extends Controller
{
    protected $statusCode = 200;
    protected $fractal;

    function __construct(Manager $fractal)
    {
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new ArraySerializer());

        if (isset($_GET['include'])) {
            $this->fractal->parseIncludes($_GET['include']);
        }
    }

    protected function respondWithItem($item, $callback)
    {
    	$resource = new Item($item, $callback);

    	$rootScope = $this->fractal->createData($resource);

    	return $this->respondWithArray($rootScope->toArray());
    }

    protected function respondWithCollection($collection, $callback)
    {
    	$resource = new Collection($collection, $callback);

    	$rootScope = $this->fractal->createData($resource);

    	return $this->respondWithArray($rootScope->toArray());
    }

    protected function respondWithArray(array $array, array $headers = []) 
    {
    	return response()->json($array, $this->statusCode, $headers);
    }
}
