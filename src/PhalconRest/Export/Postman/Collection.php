<?php

namespace PhalconRest\Export\Postman;

class Collection
{
    public $id;
    public $name;
    public $basePath;
    protected $requests = [];

    public function __construct($name, $basePath)
    {
        $this->id = uniqid();
        $this->name = $name;
        $this->basePath = $basePath;
    }

    public function addManyRoutes(array $routes)
    {
        /** @var \Phalcon\Mvc\Router\Route $route */
        foreach ($routes as $route) {
            $this->addRoute($route);
        }
    }

    public function addRoute(\Phalcon\Mvc\Router\Route $route)
    {
        if (@unserialize($route->getName())) {
            return;
        }

        $name = $route->getName() ?: $route->getPattern();

        $this->addRequest(new Request(
            $this->id,
            uniqid(),
            $name,
            null,
            $this->basePath . $route->getPattern(),
            $route->getHttpMethods(),
            'Authorization: Bearer {{authToken}}',
            null,
            "raw",
            static::GUID($route->getRouteId()),
            $route->getRouteId()
        ));
    }

    public function addRequest(Request $request)
    {
        $this->requests[] = $request;
    }

    public function addManyCollections(array $collections)
    {
        /** @var \PhalconRest\Api\Collection $collection */
        foreach ($collections as $collection) {
            $this->addCollection($collection);
        }
    }

    public function addCollection(\PhalconRest\Api\Collection $collection)
    {
        foreach ($collection->getEndpoints() as $endpoint) {

            $this->addRequest(new Request(
                $this->id,
                uniqid(),
                $collection->getPrefix() . $endpoint->getPath(),
                $endpoint->getDescription(),
                $this->basePath . $collection->getPrefix() . $endpoint->getPath(),
                $endpoint->getHttpMethod(),
                'Authorization: Bearer {{authToken}}',
                null,
                "raw",
                static::GUID($collection->getIdentifier()),
                $collection->getIdentifier()
            ));
        }
    }

    public function getRequests()
    {
        return $this->requests;
    }
    
    private static function GUID($collectionIdent)
    {
        static $guids = [];
        
        if(array_key_exists($collectionIdent, $guids)) {
            return $guids[$collectionIdent];
        }
        
        if (function_exists('com_create_guid') === true) {
            $guid = trim(com_create_guid(), '{}');
        } else {
            $guid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        }
        
        $guids[$collectionIdent] = $guid;
        
        return $guid;
    }
}
