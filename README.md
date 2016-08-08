# What is this package?
This package was created to help decrease the time it takes to build a project
which needs to create, delete, and edit the project's [Laravel Model](http://laravel.com/docs/master/eloquent)s.

# Warden?
The project is named Warden because much like a prison warden, it can bring in an inmate (create a new item), take out an inmate (delete an item), or make changes to what the inmate can do
(edit an item). By no means is it limited to just a User's model. It (so far) can work an any model.

# What do I need to do to make it work?
All you need is to do the following, then add your models to the `config/kregel/warden.php`
 
  1.  In your command line execute `composer require kregel/warden`.
  2.  Register the service provider with your `config/app.php` file
  
  ```php
  'providers' => [
    ...,
    Kregel\Warden\WardenServiceProvider::class,
    ...,
  ]
  ```
  3.  Publish the config file! This should be able to be done with `php artisan vendor:publish` once published, add in your
      models to the `config/kregel/warden.php` file and tweak it they way you need it.. 

###### Keep in mind that the first key located in the 'models' array is the name of your model that will be referenced when you use the API.

```php
'models' => [
    'user' => [
        'model' => App\User::class,
        'relations'  => [
            'update' => function($user){
                \Log::info('A users roles has been updated');
            },
            'new' => function ($user){
                \Log::info('A users role has been created');
            },
            'delete' => function ($user){
                \Log::info('A users role has been deleted/removed');
            }
        ]
    ],
    ...,
],
```
#### What's the deal with the relations array?
Well, that would be the relation events. When you do when of those events using warden, that given event will fire.
#### Why not just use model events?
I chose not to recommend model events because I had some use cases that needed different events.  If you need an action to happen to every new, updated, or deleted instance of a given model
then I'd say use the global [Eloquent Events](https://laravel.com/docs/master/eloquent#events). Otherwise if you want to be able to control who and 
 what gets to have an action take place then use the warden events (warden events only work if you're and whom ever else is using warden to edit, delete, and create models.)
  4.  Once you have everything installed and configured, you actually can navigate to your website by going to http://(yourwebsite.com)/warden/(yourmodel)s/manage
      So that means that if I had this installed on my website and set up with my users model I would go to https://austinkregel.com/warden/users/manage

### Extra features?
Out of the box, there is a `Kregel\Warden\Traits\Wardenable` trait, which can be used when you want an api responsive system. What it does it mean (in this case) to keep your api 
responsive while you edit your values, maybe change what was previously labeled `id` to something labeled `uuid` but all your clients use the label `id`. Well now you can just change 
your `$warden` variable from looking like 

```php 
protected $warden = ['id' => 'id', /* Rest of your vars...  */]; 
``` 

to... 

```php 
protected $warden = ['uuid' => 'id', /* rest of your vars... */]; 
``` 

What the client would see in the api is just the 'id' field same as before, but your internal database can change as much as you need it to. Sounds simple right?

=======

### Using the API
You can use Warden to handle your API for creating, deleting, and updating models.

If you're looking to create a new item you can use the following route as your form's action

    {{ route('warden::api.create-model', $your_model_name) }}
     
If you want to update your model, you can just put this as your form's action

    {{ route('warden::api.update-model', $your_model_name) }}
    
 Be sure to remember that when you do something other than a post/get you MUST use the pseudo input named _method and a value of PUT, DELETE, or really which ever method you need.

# Questions?
Email me, my email is on [my github page](http://github.com/austinkregel), or you can make an issue. :)
