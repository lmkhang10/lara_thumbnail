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
    $laraXThumbnail = LaraXThumbnail();
    
    // Create the Urlbox URL
    $thumbnailUrl = $laraXThumbnail->makeThumbnail($fullPath, $directory, $type, $prefix, $width);
    
    // Generate a screenshot by loading the Urlbox URL in an img tag:
    echo '<img src="' . $thumbnailUrl . '" alt="Test thumbnail generated">'
```

If you're using Laravel and have set up the service provider, you can use the Facade provided:

```php
use LaraX\Thumbnail\Facades\LaraXThumbnail;

// Create the Urlbox URL
    $thumbnailUrl = $laraXThumbnail::makeThumbnail($fullPath, $directory, $type, $prefix, $width);
// $urlboxUrl is now 'https://api.urlbox.io/v1/API_KEY/TOKEN/png?url=example.com'
```

You can now use the result (`$urlboxUrl`) by placing it inside an `<img/>` tag as the `src` parameter.

When you load the image, a screenshot of example.com will be returned.

## Contributing

We are open to pull requests.

## Security

If you discover any security related issues, please email barryle89@gmail.com instead of using the issue tracker.

## About Urlbox

Generating thumbnail in laravel basing intervention Image

## License

The MIT License (MIT).
