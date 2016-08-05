<?php

namespace PhalconRest\Transformers\Postman;

use PhalconRest\Export\Postman\Collection as PostmanCollection;
use PhalconRest\Transformers\Transformer;

class CollectionTransformer extends Transformer
{
    const FOLDERS = false;
    
    protected $defaultIncludes = [
        'requests',
    ];

    public function transform(PostmanCollection $collection)
    {
        $requests = $collection->getRequests();

        $folders = [];
        $order = [];

        foreach ($requests as $req) {
            $order[] = $req->id;
            if (($i = array_search($req->folder, array_column($folders, 'id'))) === false) {
                $folders[] = [
                    'id' => $req->folder,
                    'name' => $req->collectionName,
                    'order' => [],
                    'owner' => $collection->owner,
                ];
            } else {
                $folders[$i]['order'][] = $req->id;
            }
        }

        $data = [
            'id' => $collection->id,
            'name' => $collection->name,
            'owner' => $collection->owner,
            'public' => false,
            'published' => false,
            'timestamp' => 0,
            'order' => $order,
        ];
        
        if(static::FOLDERS) {
           $data['folders'] = $folders; 
        }
        
        return $data;
    }

    public function includeRequests(PostmanCollection $collection)
    {
        return $this->collection($collection->getRequests(), new RequestTransformer);
    }
}