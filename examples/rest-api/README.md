# phpmf Rest-API example

Refer to the code which I think is commented enough. [index.php](./index.php)

## Installation

Best is to copy the [MF.php](../../src/MF.php) to this folder and follow the rewrites
guides. Setting up Apache2.4 or Nginx for any CMS which handled Search Engine Friendly URL depending on rewrites should be sufficient for phpMF also to work.

Please note that if you are setting the rest-api/ as the document root, then the

```php
MF::set('urimask' ,'/api');
```
should be changed to 

```php
MF::set('urimask' ,'');
```
But if you are configuring this into a sub directory, then the same should be configured as the value for 'urimask'.

## Tests and Output

Note that I was doing the testing with url http://localhost:9080/ and my application server was Apache 2.4 with php 7.2 on Ubuntu 18.04 and Docker.

```
curl -X POST -H "Content-Type: application/json" -d '{"a": 10, "b": 5}' "http://localhost:9080/api/add"

{"success":true,"res":15}
```

Now with a verbose tag the PUT Method is attempted, note the response headers do have the "Content-Type: application/json" which we added through the hook on index.php line 21.

```
curl -vv -X PUT -H "Content-Type: application/json" -d '{"a": 10, "b": 5}' "http://localhost:9080/api/add"
*   Trying 127.0.0.1...
* TCP_NODELAY set
* Connected to localhost (127.0.0.1) port 9080 (#0)
> PUT /api/add HTTP/1.1
> Host: localhost:9080
> User-Agent: curl/7.58.0
> Accept: */*
> Content-Type: application/json
> Content-Length: 17
> 
* upload completely sent off: 17 out of 17 bytes
< HTTP/1.1 200 OK
< Date: Sat, 04 Apr 2020 16:40:34 GMT
< Server: Apache/2.4.29 (Ubuntu)
< Cache-Control: no-cache, must-revalidate
< Content-Length: 25
< Content-Type: application/json
< 
* Connection #0 to host localhost left intact
{"success":true,"res":15}
```

The GET Method

```
curl -X GET "http://localhost:9080/api/sub?a=100&b=80"
{"success":true,"res":20}
```

And finally the DELETE Method, the id expected is numeric and it shows the error. The {proxy+} is captured using the (.+) in the MF::MFR on index.php line 33.

```
curl -X DELETE "http://localhost:9080/api/item/delete_id"
{"success":false,"message":"Item to delete not specified"}

curl -X DELETE "http://localhost:9080/api/item/327685"
{"success":true,"message":"Delete item with ID: 327685"}
```