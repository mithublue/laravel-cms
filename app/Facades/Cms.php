<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static int|null currentUserId()
 * @method static int|null get_current_user_id()
 * @method static \Illuminate\Contracts\Auth\Authenticatable|null currentUser()
 * @method static \Illuminate\Contracts\Auth\Authenticatable|null get_current_user()
 * @method static void setCurrentObject(mixed $object)
 * @method static mixed currentObject()
 * @method static mixed get_current_obj()
 * @method static array menuTree(string|int $locationOrIdOrName)
 * @method static array menu(string|int $locationOrIdOrName)
 * @method static mixed setting(string $key, mixed $default = null)
 * @method static bool moduleEnabled(string $name)
 */
class Cms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Support\Cms::class;
    }
}
