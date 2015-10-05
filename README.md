# What is this package?
This package was created to help decrease the time it takes to echo out 
a form relating to a given [Model](http://laravel.com/docs/master/eloquent) 
while still giving the developer the ultimate amount of flexibility . 

# What do I need to do to make it work?
All you need is to do the following, then add your models to the `config/warden.php`
 
  1.  In your command line execute `composer require kregel/warden` or add `"kregel/warden":"dev-master"` to your composer.json 
      file, just be sure to use `composer update` with that statement, 
      or if you haven't build your dependancies use `composer install` instead.

  2.  Register the service provider with your `config/app.php` file
  
  ```php
  'providers' => [
    ...,
    Kregel\Warden\WardenServiceProvider::class,
    ...,
  ]
  ```
  3.  Publish the config file! This should be able to be done with `php artisan vendor:publish` once published, add in your
      models to the `config/warden.php` file and tweak it they way you need it..
  4.  Once you have everything installed and configured, you actually can navigate to your website by going to http://<yourwebsite.com>/warden/<yourmodel>s/manage
      So that means that if I had this installed on my website and set up with my users model I would go to http://austinkregel.com/warden/users/manage
# Questions?
Email me (my email is on [my github page](http://github.com/austinkregel)), or you can drop an issue. :)
