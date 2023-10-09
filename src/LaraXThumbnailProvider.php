<?php

namespace LaraX\Thumbnail;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class LaraXThumbnailProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton( LaraXThumbnail::class, function ( $app ) {
            $prefix            = config( 'larax.THUMBNAIL.PREFIX', 'thumbnail_' );
            $width         = config( 'larax.THUMBNAIL.SIZE.width', 267 );
            $height = config( 'larax.THUMBNAIL.SIZE.height', null );

            if ( ! $prefix || (! $width && ! $height) ) {
                throw new InvalidArgumentException( 'Please ensure you have set values for `larax.THUMBNAIL.PREFIX` and `larax.THUMBNAIL.SIZE.width` or `larax.THUMBNAIL.SIZE.height`' );
            }

            return new LaraXThumbnail( $prefix, $width, $height, $app->make( Client::class ) );
        } );

        $this->app->alias( Intervention\Image\Facades\Image::class, 'Image' );
        $this->app->alias( LaraXThumbnail::class, 'laraxthumbnail' );
    }
}
