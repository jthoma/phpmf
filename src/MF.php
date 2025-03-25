<?php

/**
 * @package MariaFramework
 * @author Jiju Thomas Mathew <jijutm@gmail.com>
 */

/**
 * class MF
 * @description The main class which provides the framework
 */

class MF
{

  /** this is just the version number **/
  const Version = 0.27;

  /** @var $store array **/
  public static $store = array('addons' => 'addons/', 'plugins' => 'plugins/', 'views' => 'views/');

  /** @var $events array **/
  public static $events = array();

  /** @var $fired array **/
  public static $fired = array();

  /**
   * Set value of $var as $val into static pool
   * @ void set ( String $var, Mixed $val )
   * @param String $var Name of variable being set in the MF scope
   * @param any $val Value corresponding to the name being set in the MF scope
   */
  public static function set($var, $val)
  {
    if (in_array($var, array('addons', 'plugins', 'views'))) {
      (!is_dir($val)) && $val = self::get($var);
      $val .= (substr($val, -1) !== '/') ? '/' : '';
    }
    self::$store[$var] = $val;
  }

  /**
   * Get value of $var if stored in MF or empty string
   * @ Mixed get ( String $var )
   * @param String $var Name of the variable that is to be fetched from MF scope
   * @return any Value stored in the MF scope with key $var
   */
  public static function get($var)
  {
    return (isset(self::$store[$var])) ? self::$store[$var] : "";
  }

  /**
   * Addons are singlular files containing just functions, and the function
   * names should be the base name of the value in addon, the ‘.php’ will
   * be assumed and attached when including the file.
   * @ mixed addon ( String $addon )
   * @param String $addon
   * @return Mixed boolean false if addon does not exist
   */
public static function addon($addon)
{
    try {
        $funcname = basename($addon);
        $params = func_get_args();
        if (!function_exists($funcname)) {
            $path = (strpos($addon, '.') === 0 || strpos($addon, '/') === 0) ? '' : self::get('addons');
            $filepath = realpath($path . $addon . '.php');
            if (!$filepath || !file_exists($filepath)) {
                throw new Exception("Addon file not found: " . $path . $addon . '.php');
            }
            require_once $filepath;
        }
        return call_user_func_array($funcname, array_slice($params, 1));
    } catch (Exception $e) {
        error_log("Error in MF::addon(): " . $e->getMessage()); // Log error
        return false;
    }
}
  /**
   * Keeps, checks and makes sure that any object instance is created just
   * once. It may be called as an object singleton store. Object instances
   * are stored in the static var $bag
   * @ Mixed object ( String $item [, Boolean $newIfNotExist = true ] )
   * @staticvar array $bag
   * @param string $item
   * @param bool $newIfNotExist
   * @return instanceof class in $item
   */
  public static function object($item, $newIfNotExist = true)
  {
    static $bag = array();
    $class = basename($item);
    if (isset($bag[$class]) || array_key_exists($class, $bag) === true) {
      return $bag[$class];
    } elseif ($newIfNotExist === true) {
      if (class_exists($class, false) === false) {
        $path = ($item{
        0} == '.' or $item{
        0} == '/') ? '' : self::get('plugins');
        require($path . $item . '.php');
      }
      $bag[$class] = new $class();
      return $bag[$class];
    }
    return false;
  }

  /**
   * The routing handler, such that you could specify the
   * valid <method> <resource path> to send the system through the routes.
   * The path value set in ‘extend’ is prefixed to the $class, it is
   * instantiated through the singleton registry and the $handler method
   * of the instance is invoked. The <resource path> is applied to the
   * $_SERVER['REQUEST_URI'] assuming that the <resource path> is regular
   * expression, means you can use regular expressions, and the resulting
   * match array is passed as a parameter for the $class->handler method.
   * @ void MFR ( String $route , String $class , String $handler [, int $cacheable ] )
   * @param String $route Request Method and url patten together form the route
   * @param String $class The handler class for matching route request
   * @param String $handler The handler method that is a public method of the handler class
   * @param int $cacheable Though default is false, the value taken is unsigned integer
   *                       as minutes for caching
   * @uses MF::fireevent to fire before-handler event
   */
  public static function MFR($route, $class, $handler, $cacheable = false)
  {
    (empty($route) === true) && $route = 'GET /';
    $requestType = substr($route, 0, strpos($route, ' '));
    $route = substr($route, strpos($route, ' ') + 1);

    if (strcasecmp($requestType, $_SERVER['REQUEST_METHOD']) === 0) {
      $matches = array();
      $mask = self::get('urimask');
      $checkUri = ($route == '/') ? $_SERVER['REQUEST_URI'] : rtrim($_SERVER['REQUEST_URI'], '/');
      $tt = parse_url($checkUri);
      $checkUri = $tt['path'];
      if (!empty($tt['query']))
        parse_str($tt['query'], $_GET);
      $isMatch = stripos($checkUri , $mask . $route );
    if ($isMatch !== false) {
       if (self::object($class) !== false) {
          (!$cacheable) ? 
            header("Cache-Control: no-cache, must-revalidate") :
            header("Expires: " . gmdate("D, d M Y G:i:s T", time() + $cacheable));
          self::fireevent('before-handler', $class, $handler, $cacheable);
          self::object($class)->$handler($matches);
          self::fireevent('after-handler', $class, $handler, $cacheable);
          exit();
        }
      }
    }
  }

  /**
   * Populates any assoicative arrays as name value variables and includes the
   * '.php' template file. Means the templates are rendered using raw php.
   * @param String $view Name of the template file, '.php' is attached
   * @return Boolean
   * @uses MF::fireevent to fire before-display event
   * @uses MF::fireevent to fire after-display event
   */
  public static function display($view)
  {
    self::fireevent('theme-detect', $view);
    $path = self::get('views');
    if (is_file($path . $view . '.php') === true) {
      for ($i = 1; $i < func_num_args(); $i++) {
        $argument = func_get_arg($i);
        if (is_array($argument) === true) {
          extract($argument);
        }
      }
      /** @var array $global */
      $global = self::get('global');
      (!empty($global)) && extract($global);
      self::fireevent('before-display', $view);
      require($path . $view . '.php');
      self::fireevent('after-display', $view);
      return true;
    }
    return false;
  }

  /**
   * Since by application server configuration, all not found resources are
   * rewritten to our handler, and we dont want genuine not found to return
   * HTTP 200 and then an empty page. So the handling of a 404 header, with
   * an optional neatly formatted page would be the best.
   * @ void run ( [ $page = '' ] )
   * @param String $page Optional formatted 404 page, else no content will be sent
   */
  public static function run($page = '', $code = 404)
  {
    // stupid.. normally run would have completed earlier.. 
    if (headers_sent() === false) {
      header("HTTP/1.0 " . $code, true, $code);
    }
    if (!empty($page)) {
      self::display($page);
    }
    exit();
  }

  /**
   * Intenal or external redirects will be required in a portal for
   * maintaining old structure or page ranks and for many other purpose too
   * @ void redirect ( String $url [, bool $permanent = false ] )
   * @param String $url Will redirect to this url
   * @param Bool $permanent if true, http 301 else 302 is the status code.
   * @uses MF::fireevent to fire before-redirect event
   */
  public static function redirect($url, $permanent = false)
  {
    if (headers_sent() === false) {
      self::fireevent('before-redirect', $url, $permanent);
      header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }
    exit();
  }

  /**
   * Idea got from wordpress, but a bit different. Any way this is for plugins to exist and
   * attach their methods to events which are fired using MF::fireevent
   * @ void addaction ( String $name, array $handler [ int $priority  = 10] )
   * @param String $name Name of the action, this should be fired with MF::fireevent where the event happens
   * @param array $handler Handler is array ( class, method )
   * @param int $priority int range 1 - 999
   */
  public static function addaction($name, $handler, $priority = 10)
  {
    if (!isset(self::$fired[$name])) {
      $key = is_array($handler) ? join('::', $handler) : $handler;
      if (isset(self::$events[$name][$key])) {
        self::$events[$name][$key]['priority'] = $priority;
      } else {
        self::$events[$name][$key] = array('method' => $handler, 'priority' => $priority);
      }
    }
  }

  /**
   * The real event handler, enables plugins to generate their own events
   * and to fire built in events. Themes can also have events to be fired
   * such that plugins can hook actions to those events.
   * @param String $name the name of the event.
   * @uses MF::object to instantiate the handler
   * @uses MF::prioritize to sort actions in each event to the priority added
   */
  public static function fireevent($name)
  {
    if (!isset(self::$fired[$name])) {
      if (!empty(self::$events[$name])) {
        uasort(self::$events[$name], 'MF::prioritize');

        $args = func_get_args();
        foreach (self::$events[$name] as $method) {
          if(is_array($method['method'])){
            $h = $method['method'][0];
            $c = $method['method'][1];
            self::object($h)->$c($args);  
          }else{
            $fn = $method['method'];
            $fn($args);
          }
        }
      }
      self::$fired[$name] = time();
    }
  }

  /**
   * Sorting function for actions in an event chain
   * @param mixed $a
   * @param mixed $b
   * @return int (-1, 0, 1)
   */
  public static function prioritize($a, $b)
  {
    return ($a['priority'] == $b['priority']) ? 0 : (($a['priority'] < $b['priority']) ? -1 : 1);
  }

}
