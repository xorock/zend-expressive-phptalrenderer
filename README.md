# PHPTAL Integration for Expressive

Provides [PHPTAL](http://phptal.org/) integration for
[Expressive](https://github.com/zendframework/zend-expressive).

## Installation

Install this library using composer:

```bash
$ composer require xorock/zend-expressive-phptalrenderer
```
We recommend using a dependency injection container, and typehint against
[container-interop](https://github.com/container-interop/container-interop). We
can recommend the following implementations:

- [zend-servicemanager](https://github.com/zendframework/zend-servicemanager):
  `composer require zendframework/zend-servicemanager`
- [pimple-interop](https://github.com/moufmouf/pimple-interop):
  `composer require mouf/pimple-interop`
- [Aura.Di](https://github.com/auraphp/Aura.Di)

## Configuration

The following details configuration specific to PHPTAL, as consumed by the
`PhptalRendererFactory`:

```php
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Expressive\Phptal\HelperManager;
use Zend\Expressive\Phptal\Helper;
use Zend\Expressive\Phptal\PhptalEngineFactory;
use PHPTAL as PhptalEngine;

return [
    'dependencies' => [
        'factories' => [
            'Zend\Expressive\FinalHandler' =>
                Zend\Expressive\Container\TemplatedErrorHandlerFactory::class,
            
            Zend\Expressive\Template\TemplateRendererInterface::class =>
                Zend\Expressive\Phptal\PhptalRendererFactory::class,
            PhptalEngine::class => PhptalEngineFactory::class,

            HelperManager::class => InvokableFactory::class,
            Helper\UrlHelper::class => Helper\UrlHelperFactory::class,
            Helper\ServerUrlHelper::class => Helper\ServerUrlHelperFactory::class,
        ],
    ],

    // if enabled, forces to reparse templates every time
    'debug' => boolean,
    
    'templates' => [
        'extension' => 'file extension used by templates; defaults to html',
        'paths' => [
            // Paths may be strings or arrays of string paths.
        ],
        'paths' => 'templates' // Defaults to `templates` directory
    ],

    'phptal' => [
        'cache_dir' => 'path to cached templates',
        // if enabled, delete all template cache files before processing
        'cache_purge_mode' => boolean,
        // set how long compiled templates and phptal:cache files are kept; in days 
        'cache_lifetime' => 30,
        'encoding' => 'set input and ouput encoding; defaults to UTF-8',
        // one of the predefined constants: PHPTAL::HTML5,  PHPTAL::XML, PHPTAL::XHTML
        'output_mode' => PhptalEngine::HTML5,
        // set whitespace compression mode
        'compress_whitespace' => boolean,
        // strip all html comments
        'strip_comments' => boolean,
        'helpers' => [
            // helper service names or instances
        ]
    ],
];
```

## Included helpers and functions

The included `HelperManager` adds support for using own functions inside templates proxying built-in `helper`
custom expression modifier to user class. User class has to implement `HelperInterface` and `__invoke()` method.

The following template helpers are automatically activated if UrlHelper and ServerUrlHelper are registered 
with the container:

- ``url``: Shortcut for [UrlHelper](https://github.com/zendframework/zend-expressive/blob/master/doc/book/features/helpers/url-helper.md)

    ```html
    <a tal:attributes="href helper:url('article_show', ['id' => 3])">Link</a>
    Generates: /article/3
    ```

- ``serverurl``: Shortcut for [ServerUrlHelper](https://github.com/zendframework/zend-expressive/blob/master/doc/book/features/helpers/server-url-helper.md)

    ```html
    <a tal:attributes="href helper:serverurl('/foo')">Link</a>
    Generates: /foo
    ```

As an example we can create own helper based on DateTime object:

```php
use DateTime;
use Zend\Expressive\Phptal\Helper\HelperInterface;

class DateTimeHelper implements HelperInterface
{
    const HELPER_NAME = 'datetime';
    
    public function __invoke(DateTime $datetime = null)
    {
        if ($datetime === null) {
            $datetime = new DateTime();
        }
        return $datetime->format(DateTime::ISO8601);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getHelperName()
    {
        return self::HELPER_NAME;
    }
}
```

Now we need to pass it to configuration array:

```php
'dependencies' => [
    'aliases' => [
        'dateTimeHelper' => DateTimeHelper::class,
    ],
    'factories' => [
        DateTimeHelper::class => DateTimeHelperFactory::class,
    ],
],

'phptal' => [
    'helpers' => [
        DateTimeHelper::class, // or 'dateTimeHelper' alias
    ]
]
```

Then pass new DateTime from SomeAction to our template:

```php
$date = new \DateTime();
$data['date'] = $date;
$this->template->render('app::home-page', $data)
```

And inside template:

```html
${helper:datetime(date)}
Will show current date in ISO 8601 format
```
