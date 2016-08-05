<?php

namespace PhalconRest\Transformers\Postman;

use PhalconRest\Export\Postman\Collection as PostmanCollection;
use PhalconRest\Transformers\Transformer;

class CollectionTransformer extends Transformer
{
    protected $defaultIncludes = [
        'requests',
    ];

    public function transform(PostmanCollection $collection)
    {
        $requests = $collection->getRequests();

        $folders = [];

        foreach ($requests as $req) {
            if (($i = array_search($req->folder, array_column($folders, 'id'))) === false) {
                $folders[] = [
                    'id' => $req->folder,
                    'name' => $req->collectionName,
                    'orders' => [],
                ];
            } else {
                $folders[$i]['orders'][] = $req->id;
            }
        }

        return [
            'id' => $collection->id,
            'name' => $collection->name,
            'folders' => $folders,
        ];
    }

    public function includeRequests(PostmanCollection $collection)
    {
        return $this->collection($collection->getRequests(), new RequestTransformer);
    }
}