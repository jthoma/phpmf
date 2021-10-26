<?php

/**
 * @package MariaFramework
 * @subpackage info plugin
 * @description helloworld
 */

class info
{

  public function index()
  {
    $dummy = base64_decode('PGgyPjxhIG5hbWU9Im1vZHVsZV9sYW1iZGEiPmxhbWJkYTwvYT48L2gyPgo8dGFibGU+Cjx0cj48dGQgY2xhc3M9ImUiPmF3cyBzYW0gdmVyc2lvbiA8L3RkPjx0ZCBjbGFzcz0idiI+U0FNIENMSSwgdmVyc2lvbiAwLjUxLjA8L3RkPjwvdHI+Cjx0cj48dGQgY2xhc3M9ImUiPnJlZ2lvbiA8L3RkPjx0ZCBjbGFzcz0idiI+dXMtZWFzdC0xIDwvdGQ+PC90cj4KPC90YWJsZT4KPGgyPjxhIG5hbWU9Im1vZHVsZV9kYXRlIj5kYXRlPC9hPjwvaDI+Cgo=');
    ob_start();
    phpinfo();
    $info = ob_get_clean();
    echo str_replace('<h2><a name="module_date">date</a></h2>', $dummy, $info);
  }
}
