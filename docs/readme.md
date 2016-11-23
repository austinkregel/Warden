## What is [Warden](https://github.com/austinkregel/warden)?
Warden is a dynamic model manager for the [Laravel Framework](https://laravel.com)

## What makes Warden's forms?
Warden was build using another package of mine called [PaperWork](https://github.com/austinkregel/formmodel) formerly called FormModel. PaperWork is a dynamic CRUD generator. If you're interested in PaperWork, 
the [documentation is poorly written](https://github.com/austinkregel/formmodel/tree/master/docs)... (sorry about that... I'm working on it...)

## Installing Warden.

To install Warden,
```bash
composer require kregel/warden
```

Then once composer finishes, you must add Warden to the array of service providers in your `/config/app.php` file.

```php
Kregel\Warden\WardenServiceProvider::class,
```

Once the WardenServiceProvider is registered, you'll need to publish the configuration. You can do that with the following artisan command
```bash
php artisan vendor:publish --provider='Kregel\Warden\WardenServiceProvider'
```



## So... How do I use this?...

#### First
To start using Warden, you must open your `/config/kregel/warden.php`, and add a friendly name to your `models` array (it should be an associative array.). If you're making a model for blog post you might want use just use
`blog` or `post` (I'll just call it `post` for the reminder of the docs). Then inside the `post` array you must have a key/value pair of `'model'` and the class name.
   
So as an example,
```php
'models' => [
    'post' => [
        'model' => App\Post::class    
    ]
]
```

#### Second
You must make sure that your model has fillable attributes. If it doesn't have anything in it's fillable array then Warden won't have any way to input any data to your model.

So for our example we might want 

```php
protected $fillable = [
    'title', 'body', 'is_published', // And other attributes
];
```

#### Third
Go to your website `http://example.com/warden`. On the left side you'll see a list of registered models. Click on the one you want, for this demo it would be `post`.
Then click `New Post`, fill out your information for your model and click Submit. You can view your new model by 
click on the desired model, and then clicking `List all Posts`

#### If you encounter any issues...
Some things to keep in mind...
* Make sure you ran a migration.
* Double check your spelling. 
* Make sure the keys in the models array, are all lower case and are all singular.
* **Make sure you set your fillable**


#### What are some other ways to use it?
There are many ways to use Warden.

The most common way I use Warden is as a quick and dirty PHPMyAdmin, but for Laravel.

Another way I use warden is for a backend api. By this I mean, I put the route for 
creating the given model in a form. I would not recommend it for production, but it's great for prototyping!
  