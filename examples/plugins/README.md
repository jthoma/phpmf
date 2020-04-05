# phpMF Example Plugins

Plugins are basically custom php classes specifically written with grouped generic functionality.

* [bodyparser](#bodyparser)
* [mysqli](#mysqli)

<a name="bodyparser"></a>
## Body Parser

Class with a method *getjson* which first verifies that the request content-type header is specified as 'application/json' and then tries to capture the <strong>POST BODY</strong> and attempts to decode the same by default the return value will be type array, but if you require as object pass ```false``` as the first argument to the *getjson* method.

<a name="mysqli"></a>
## Mysql-I 

This class wraps up the mysqli* functions using the procedural way. And a lot of helper methods are added on top of the php native functions. Almost all the native functions are available as methods, and custom methods are integrated with wrapping. A cahcing plugin can be adopted to utilize intemediate caching either on disk (file-cache) or in memory (memecache, elasticcache).

### ::connect ( string $dsn, object $cache )

Initialize the plugin using the following syntax

```php
$dsn = 'mysql://user:pass@server/db';
$cache = MF::object('MFCache');

MF::object('mysql')->connect($dsn, $cache);
```

The dsn can be tricky if the password contains ':' or '@', and in that case the url_encode / url_decode may be needed, but since mostly we were handling the infrastructure and firewall, we were the least bothered and used to create db passwords within our limitation.

#### Hooks

The *::connect* method fires an event <strong>mysql-connect</strong> just after conneting and it passes the link as the first parameter, this could be a hook to attach any database profiling tools, [Open PHP-MyProfiler](http://www.php-trivandrum.org/open-php-myprofiler/) is one of my own creation. The closeconnection, which is a private method also fires a complemntary event <strong>mysql-disconnect</strong>, which could be where the profiling could be stopped.

### ::query ( string $query )

Generally hanles all queries, and any raw queries can be passed, just like that. 

### ::perform ( string $table, assoc $data, [[insert|replace|update] $action], [string $condition] )

The *::perfom* method is used to make changes to the data using one of the three methods, viz: INSERT, REPLACE, UPDATE.

### ::getData ( string $query, [row|array|object] $type = row, int $ttl = 0 ])

### ::getColumn ( string $query, int $column = 0, int $ttl = 0 ])

### ::getValue ( string $query, int $ttl = 0)

### ::getRow ( string $query, [row|array|object] $type = row, int $ttl = 0 ])

### ::getPage ( string $fields, string $tables, string $condition, int $page, int $perpage, [row|array|object] $type = 'array')