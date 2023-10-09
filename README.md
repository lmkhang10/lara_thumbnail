## Installation

This package can be installed through Composer.

```bash
composer require LaraX/thumbnail
```


### Laravel Setup

When using Laravel, if you are using a version pre v5.5 you will need to include the Service Provider manually:

```php
// app/config/app.php

'providers' => [
    // ...
    'LaraX\Screenshots\LaraXProvider'
];
```

Setup your API keys:

```php
// config/larax.php

'THUMBNAIL'  =>  [
    'PREFIX'        =>  'thumbnail_',
    'SIZE'          =>  [
        'width'     =>  267,
        'height'    => null
    ]
];
```

and in your .env file:

```bash
# THUMBNAIL
GENERATE_THUMBNAIL=TRUE
```

## Usage

Here is a sample call to generate a LaraXThumbnail screenshot URL:

```php
    use LaraX\Thumbnail\LaraXThumbnail;
    $LaraXThumbnail = new LaraXThumbnail();
    $thumbnailUrl = $LaraXThumbnail->makeThumbnail(storage_path('app/public/'. $folder .$newFileName), $folder, 'public', 'thumbnail_', 240);
    
    // Generate a screenshot by loading the Urlbox URL in an img tag:
    echo '<img src="' . $thumbnailUrl . '" alt="Test thumbnail generated">'
```

If you're using Laravel and have set up the service provider, you can use the Facade provided:

```php
use LaraX\Thumbnail\LaraXThumbnail;

// Create the thumbnail URL
$LaraXThumbnail = new LaraXThumbnail();
$thumbnailUrl = $laraXThumbnail->makeThumbnail(storage_path('app/public/'. $folder .$newFileName), $folder, 'public', 'thumbnail_', 240);
```

You can now use the result (`$thumbnailUrl`) by placing it inside an `<img/>` tag as the `src` parameter.

## Contributing

We are open to pull requests.

## Security

If you discover any security related issues, please email barryle89@gmail.com instead of using the issue tracker.

## About LaraXThumbnail

Generating thumbnail in laravel basing intervention Image

## License

The MIT License (MIT).
