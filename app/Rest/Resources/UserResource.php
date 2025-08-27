<?php

namespace App\Rest\Resources;

use App\Models\Product;
use App\Rest\Resource as RestResource;
use Lomkit\Rest\Relations\BelongsToMany;
// use Illuminate\Database\Eloquent\Relations\HasMany;
use Lomkit\Rest\Relations\HasMany;

class UserResource extends RestResource
{

    use \Lomkit\Rest\Concerns\Resource\DisableGates;
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    public static $model = \App\Models\User::class;

    /**
     * The exposed fields that could be provided
     * @param RestRequest $request
     * @return array
     */
    public function fields(\Lomkit\Rest\Http\Requests\RestRequest $request): array
    {
        return [
            'id',
            'fname',
            'lname',
            'name',
            'current_location',
            'residential',
            'phone',
            'email',
            'image'
        ];
    }


    public function createRules(\Lomkit\Rest\Http\Requests\RestRequest $request) {
        return [
            'fname' => 'required'
        ];
    }
    /**
     * The exposed relations that could be provided
     * @param RestRequest $request
     * @return array
     */
    public function relations(\Lomkit\Rest\Http\Requests\RestRequest $request): array
    {
        return [
            HasMany::make('reviews',ReviewResource::class),
            HasMany::make('subscriptions',SubscriptionResource::class),
            HasMany::make('cards',CardResource::class),
            HasMany::make('shoppingLists',ReviewResource::class),
            BelongsToMany::make('favoriteProducts', ProductResource::class)
            // HasOne::make('hasOneRelation', HasOneResource::class),
            // BelongsTo::make('belongsToRelation', BelongsToResource::class),
            // HasMany::make('hasManyRelation', HasManyResource::class),
            // BelongsToMany::make('belongsToManyRelation', BelongsToManyResource::class)
            //     ->withPivotFields(['created_at']),
        ];
    }

    /**
     * The exposed scopes that could be provided
     * @param RestRequest $request
     * @return array
     */
    public function scopes(\Lomkit\Rest\Http\Requests\RestRequest $request): array
    {
        return [];
    }

    /**
     * The exposed limits that could be provided
     * @param RestRequest $request
     * @return array
     */
    public function limits(\Lomkit\Rest\Http\Requests\RestRequest $request): array
    {
        return [
            10,
            25,
            50
        ];
    }

    /**
     * The actions that should be linked
     * @param RestRequest $request
     * @return array
     */
    public function actions(\Lomkit\Rest\Http\Requests\RestRequest $request): array {
        return [];
    }

    /**
     * The instructions that should be linked
     * @param RestRequest $request
     * @return array
     */
    public function instructions(\Lomkit\Rest\Http\Requests\RestRequest $request): array {
        return [];
    }

    // public function mutated(MutateRequest $request, array $requestBody, User $model)
    // {
    //     if ($requestBody['operation'] === 'update') {
    //         Storage::put('images/avatars/'.$model->getKey().'.jpg', $requestBody['attributes']['file'])
    //     }
    // }

    // public function mutating(MutateRequest $request, array $requestBody, User $model)
    // {
    //     if ($requestBody['operation'] === 'update') {
    //         Storage::put('images/avatars/'.$model->getKey().'.jpg', $requestBody['attributes']['file'])
    //     }
    // }

    // public function destroyed(MutateRequest $request, array $requestBody, User $model)
    // {
    //     if ($requestBody['operation'] === 'update') {
    //         Storage::put('images/avatars/'.$model->getKey().'.jpg', $requestBody['attributes']['file'])
    //     }
    // }

    // public function restoring(MutateRequest $request, array $requestBody, User $model)
    // {
    //     if ($requestBody['operation'] === 'update') {
    //         Storage::put('images/avatars/'.$model->getKey().'.jpg', $requestBody['attributes']['file'])
    //     }
    // }

    // public function restored(MutateRequest $request, array $requestBody, User $model)
    // {
    //     if ($requestBody['operation'] === 'update') {
    //         Storage::put('images/avatars/'.$model->getKey().'.jpg', $requestBody['attributes']['file'])
    //     }
    // }

    // public function forceDestroying(MutateRequest $request, array $requestBody, User $model)
    // {
    //     if ($requestBody['operation'] === 'update') {
    //         Storage::put('images/avatars/'.$model->getKey().'.jpg', $requestBody['attributes']['file'])
    //     }
    // }

    // public function forceDestroyed(MutateRequest $request, array $requestBody, User $model)
    // {
    //     if ($requestBody['operation'] === 'update') {
    //         Storage::put('images/avatars/'.$model->getKey().'.jpg', $requestBody['attributes']['file'])
    //     }
    // }
}
