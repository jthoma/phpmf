# phpMF Example Plugins

Plugins are basically custom php classes specifically written with grouped generic functionality.

* [formatvalidator](#formatvalidator)
* [bodyparser](#bodyparser)
* [mysqli](#mysqli)

<a name="formatvalidator"></a>
## format validator

Class helper for input format validation, further rule updations may be required other than what is provided. 

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

The *::connect* method fires an event <strong>mysql-connect</strong> just after conneting and it passes the link as the first parameter, this could be a hook to attach any database profiling tools, [Open PHP-MyProfiler](http://www.php-trivandrum.org/open-php-myprofiler/) is one of my own creation. The closeconnection, which is a private method also fires a complimentary event <strong>mysql-disconnect</strong>, which could be where the profiling could be stopped.

### ::query ( string $query )

Generally handles all queries, and any raw queries can be passed, just like that. 

### ::perform ( string $table, assoc $data, [[insert|replace|update] $action], [string $condition] )

The *::perfom* method is used to make changes to the data using one of the three methods, viz: INSERT, REPLACE, UPDATE.

### ::getData ( string $query, [[row|array|object] $type = row], [ int $ttl = 0 ])

Pass a SELECT query, and expect an array of arrays ( or objects ) as accordingly the second parameter specifies, 'row' (the default) will return array of arrays with numerical indexes, 'array' will return array of associative arrays with field names as indices, and 'object' will return array of std class object with field names as object properties. For the last option, don`t forget to specify field aliases, where ever conditional or complicated selection is inserted. The last parameter specifies the caching ttl, and is expected in seconds. Till date cache invalidateion is not implemented, hence the ttl should be well thought of if using the cache plugin. 

### ::getColumn ( string $query [, int $column = 0, int $ttl = 0 ])

As the name depicts, one can pass a SELECT query and fetch any arbitrary column as a numerical indexed array, which may be useful at certian occassions, the ttl caches the whole result set. Hence two fields can be specified in a query, with just changing the column value, which speeds up the data fetch.

### ::getValue ( string $query, int $ttl = 0)

This just gets single row, single column and you can only pass single field in the SELECT query that should be passed. 

### ::getRow ( string $query, [row|array|object] $type = row, int $ttl = 0 ])

To fetch a single row, as numerical or associative or std class object, the select query, type and caching ttl is to be passed.

### ::getPage ( string $fields, string $tables, string $condition, int $page, int $perpage, [row|array|object] $type = 'array')

This could be the most used one, and since data changes are expected too frequenly the caching is not implemented for this helper method. To get data as a paginated list, with passing the first argument string containing comma separated field names, then the table names, if multiple tables, the join criteria and conditions all inclusive, then the combined conditionals, the page number to be displayed, items per page and type of return value, the default being array of associative arrays.

The return value is an associated array which could be used to display any paginated tabular data or to load data into [datatables](https://datatables.net/). The return is of structure as follows

```php
array('total' => $count, 'pages' => $pages, 'current' => $page, 'range' => $range, 'data' => $data);
```
To explain the above, total is total items existing within the selection criteria, pages is the number of pages when items per page is the passed $perpage, current is just the value of displayed page, range is range of item numbers (eg: 1 - 20), and data is the actual table data.
