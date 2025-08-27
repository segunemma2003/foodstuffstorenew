<?php

namespace App\Rest\Controllers;

use App\Rest\Controller as RestController;

class ReviewController extends RestController
{
    /**
     * The resource the controller corresponds to.
     *
     * @var class-string<\Lomkit\Rest\Http\Resource>
     */
    public static $resource = \App\Rest\Resources\ReviewResource::class;
}
