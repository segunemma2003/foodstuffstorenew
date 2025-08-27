<?php

namespace App\Rest\Controllers;

use App\Rest\Controller as RestController;

class UsersController extends RestController
{
    /**
     * The resource the controller corresponds to.
     *
     * @var class-string<\Lomkit\Rest\Http\Resource>
     */
    public static $resource = \App\Rest\Resources\UserResource::class;

    // protected function  beforeDetails(DetailsRequest $request){}
    // protected function beforeSearch(){}
    // protected function afterSearch(){}
    // protected function beforeMutate(){}
    // protected function afterMutate(){}
    // protected function beforeOperate(){}
    // protected function afterOperate(){}
    // protected function beforeDestroy(){}
    // protected function afterDestroy(){}
    // protected function beforeForceDestroy(){}
    // protected function afterForceDestroy(){}
}
