<?php

namespace LaraX\Thumbnail\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Datatables.
 *
 * @package LaraX\Thumbnail\Facades
 * @author Barry Le <barryle89@gmail.com>
 */
class LaraXThumbnail extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laraxthumbnail';
    }
}
