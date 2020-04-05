# phpmf
light weight php framework with minimal bloat

```php
/**
 * /index.php
 */
require "MF.php";

MF::MFR('GET /', 'hello', 'world');
MF::run('404');


/**
 * /plugins/hello.php
 */
class hello 
{
  public function world(){
    echo 'Hello World';
  }
} 

```

## Installation

Nothing much other than probably just like for all other dynamic frameworks, the rewrite should process index.php to handle all requests. Some samples for [apache htaccess](examples/rewrites/apache2.txt) and [nginx server block](examples/rewrites/nginx.txt) have been added.

If starting from scratch, use the src/ as the document root and retain the structure. But if you need to change the structure, feel free to do so and update the paths as below.

```php
MF::set('addons','/full/path/to/addons');
MF::set('plugins','/full/path/to/plugins');
MF::set('views','/full/path/to/views');
```

## Methods

### MF::set

```php
MF::set('var-name','var-value');
```

Set value for a named variable inside the MF store custom scope.

### MF::get

```php
MF::get('var-name');
```

Get value of a named variable from the MF store custom scope.

### MF::MFR

This is the core route defenition and supports the HTTP request method + request path with regex matches. The method takes minimum of three parameters, first is the method + request, second is the class name, and the third is the public method of the class, an optional fourth parameter will be honored only if the cache subsystem plugin is activated. There are too many to show as example code. Will update as time permits.

### MF::addon

Addons are nothing but a function with the filename as function name, but addons cannot be defined as route handlers. So generally addons are used for global settings, splitting up routes into role sections, and attaching handlers to event hooks. Also if required multiple functions can be defined in a single addon, incase all these functions will be invoked in many request events. Sample addon with multiple functions and actions attached to hooks [APM](examples/addons/apm.php), [settings addon](examples/addons/settings.php), [sessions](examples/addons/sessions.php), [themehelper](examples/addons/themehelper.php).   

For configuring some parameters, usage of a settings addon could be the best method. 

```php
MF::addon('addon_function_name', [$arg1, $arg2, ....]);
```

### MF::object

This loads any class from plugins/classname.php and methods can be operated from there. Check out the plugin [bodyparser](examples/plugins/#bodyparser), which in the following case returns the post body json parsed as an associative array. And if the argument for getjson is false or null the default behaviour of json_decode is to return ```stdClass Object```.

```php
$array = MF::object("bodyparser")->getjson(true);
```
We had been using this for long with just a [mysqli wrapper](./examples/plugins/#mysql), but as of recent there was an enquiry for support for pdo. <strong>*Yes*</strong> you can make it support pdo with singleton instance. This method makes sure that the instance is available through function scopes without importing from global.

```php
// for once somewhere intialize the pdo
MF::set('db', new PDO($dsn, $user, $pass));

// later you can use the property methods
MF::get('db')->query("SHOW TABLES");
```

### MF::display

The method loads, parses and outputs view files from the 'views/' folder. View chaining is possible to provide the facility of common header and footer. Views are simple php files with embedded echo $var or loops and if conditions. Any php code can be included in the views. ;) pardon me, some of the basics were borrowed from [wordpress](https://www.wordpress.org). The method can take any number of arbitrary arguments after the first mandatory one which is the view path '.php' extension is implied and should not be provided. All subsequent arguments are assumed to be associative arrays and will be extracted in the scope of the view rendering. 

### MF::run

This method is used to show custom page with custom response status. Normally used to display error 404, this essentially should be the last entry in the routing file (index.php). This renders a template from view without custom variables, though one can force the template to render with varibles if the variable array is set as 'global' like 

```php
MF::set('global', ['title' => 'Sorry the page you looked is not here' ])
```

### MF::redirect

Nothing other than the standard header("Location:" + url), but made fancy to tell whether it is permanent or temporary (301, 302).

### MF::addaction

Another functionality borrowed from [wordrpress](https://www.wordpress.org). The hangover is quite high. This appends methods to events which are registerd using MF::fireevent. The method takes three arguments which are string $action_name, callable $handler, int $priority [10]. Nothing will happen if the $action_name is not called using MF::fireevent and there is no sanity check for the same.

#### Hooks

phpMF is extensible with event hooks, and there are six predefined hooks, and any one can register new hooks using the MF::fireevent($event, $fn) or MF::fireevent($event, $class, $handler). Built in events are

* before-redirect - whenever MF::redirect is called, this will fire just before redirecting
* before-handler - any MF::MFR when traps the route before handler is invoked
* after-handler - any MF::MFR when traps the route after handler is invoked ( unless exit inside handler )
* theme-detect - any MF::display will trigger this; hook attached can switch view folder by setting 'views'
* before-display - before rendering any template, if chained all views will fire this
* after-display - after rendering any template, if chained all views will fire this

### MF::fireevent

Register new hooks, for example in the view files, if you add the following just before '&lt;/head&gt;' you have registerd a new hook, and the arguments are passed in the same sequence to the action handler if any action is registered in that hook. Action registration (MF::addaction) can add actions handlers to the hook any time before the event is fired in the sequence. 

```php
MF::fireevent('head'[, $arg1, $arg2]);
```

### MF::debug

As the name implies this is to debug the calls at any stage, for a full debug state, the settings addon or somewhere MF::set('logfile','&lt;something&gt;'), should be written, the '&lt;something&gt;' could either be a full path to a file which is writeable or it can evaluate to true ( with not writable ), in the first case the file is appended with the MF::debug() calls and the latter will display the same in '&lt;pre&gt;' tag on screen.

To use switch to the [debug version](src/MF-debug.php)

# Use Case Examples

* [Rest API](./examples/rest-api/)

# Contributing

## Documentation 

Agreed that documentation is incomplete, fork commit and do pull requests, will try to merge after review. 
For the plugins or addons which I have already provided as examples, individual mark down files will be added later on. As and when time permits. Also a couple of full fledged example applications are in the pipeline such that one can pick it and start doing. 

Some ideas for short and sweet examples would be great if some one can suggest through the issues section, I could try and make it or ask some of my trainees to do it as an assignment ;).

## Plugins

A lot of plugins could do, will be committing some of which I had created during the course of time. As of now will rest with this. I do have a very indepth plugin for wrapping mysqli to this framework, but it will need a heavy custom documentation. 

## Addons

Any number of addons can extend the features. 

## Quality process

Unit tests ( very poor at it ) are the one that is missing from this project, would appreciate if some one could start something atleast. And if we can automate using Travis and CodeCoverage analysis, think it is too much.