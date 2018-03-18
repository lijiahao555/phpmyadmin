<?php

namespace corephp;

// 框架根目录         如果没有定义则定义  类似三元运算
defined('CORE_PATH') or define('CORE_PATH', __DIR__);

/**
* 框架核心类
*/
class Corephp
{

	protected $config;

	public function __construct($config)
	{
		$this->config = $config;
	}

	// 运行程序
    public function run()
    {
    	// 类似于__autoload
        spl_autoload_register(array($this, 'loadClass'));

        // 检测开发环境
        $this->setReporting();

        // 检测敏感字符并删除
        $this->removeMagicQuotes();

        // 检测自定义全局变量并移除
        $this->unregisterGlobals();

        // 设置数据库信息
        $this->setDbConfig();

        // 路由处理
        $this->route();
    }

    // 检测开发环境
    public function setReporting()
    {
    	// 调试阶段还是 上线阶段  true false
        if (APP_DEBUG === true) {
            error_reporting(E_ALL);			// 规定不同级别的错误    E_ALL报告所有错误
            ini_set('display_errors','On');	// 设置phpini 开启错误回显，若出现错误，则报错，出现错误提示
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors','Off'); // 设置phpini 关闭错误回显，若出现错误，则提示：服务器错误。但是不会出现错误提示
            ini_set('log_errors', 'On');	 // 设置phpini 开启日志
        }
    }

    // 检测敏感字符并删除
    public function removeMagicQuotes()
    {
        if (get_magic_quotes_gpc()) {
            echo "检测敏感字符并删除";die;
            $_GET = isset($_GET) ? $this->stripSlashesDeep($_GET ) : '';
            $_POST = isset($_POST) ? $this->stripSlashesDeep($_POST ) : '';
            $_COOKIE = isset($_COOKIE) ? $this->stripSlashesDeep($_COOKIE) : '';
            $_SESSION = isset($_SESSION) ? $this->stripSlashesDeep($_SESSION) : '';
        }
    }

    // 删除敏感字符
    public function stripSlashesDeep($value)
    {
    	echo "删除敏感字符";die;
        $value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : stripslashes($value);
        return $value;
    }

    // 检测自定义全局变量并移除。因为 register_globals 已经弃用，如果已经弃用的 register_globals 指令被设置为 on，那么局部变量也将在脚本的全局作用域中可用。 例如， $_POST['foo'] 也将以 $foo 的形式存在，这样写是不好的实现，会影响代码中的其他变量。 相关信息，参考: http://php.net/manual/zh/faq.using.php#faq.register-globals
    public function unregisterGlobals()
    {
    	// register_globals的危害:会将用户提交的GET,POST参数注册成全局变量并初始化为参数对应的值
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    // 配置数据库信息
    public function setDbConfig()
    {
        if ($this->config['db']) {
            define('DB_HOST', $this->config['db']['host']);
            define('DB_USER', $this->config['db']['username']);
            define('DB_PASS', $this->config['db']['password']);
            define('DB_NAME', $this->config['db']['dbname']);
        }
    }


    // 路由处理
    public function route()
    {
    	// ucfirst 将首字母大写
        // 设置默认访问控制器方法
        $controllerName = $this->config['defaultController'] ? ucfirst($this->config['defaultController']) : 'Index';

        $actionName = 'action' . $actionName = $this->config['defaultAction'] ? ucfirst($this->config['defaultAction']) : 'Index';

        $param = [];

        // 获取 域名以后的所有URL
        $url = $_SERVER['REQUEST_URI'];

        // 删除前后的 /
        $urlDetail = trim($url, '/');

        // 清除?之后的内容 控制器和方法
        $position = strpos($urlDetail, '?');

        // 字符串长度
        $urlLenth = strlen($urlDetail);

        // (fasle没值) or (!false有值)
        $url = $position === false ? $urlDetail : substr($urlDetail, 0, $position);

        // (int有参数) or (!int没参数)
        $urlParam = is_int($position) ? substr($urlDetail, $position, $urlLenth) : '';


        // 处理控制器方法
        if ($url) {
            // 使用/分割字符串，并保存在数组中
            $urlArray = explode('/', $url);

            // 删除空的数组元素  array_filter 过滤数组 第二参数(函数)为空则过滤为空的值
            $urlArray = array_filter($urlArray);

            // 获取控制器名
            $controllerName = ucfirst($urlArray[0]);

            // 获取动作名
            array_shift($urlArray);  // array_shift 删除数组第一个【0】

            $actionName = $urlArray ? 'action' . ucfirst($urlArray[0]) : $actionName;
        }

        // 处理参数
        if ($urlParam) {
            $urlParam = trim($urlParam, '?');
            if (strpos($urlParam, '&')) {

                $urlParam = explode('&', $urlParam);
                $urlParam = array_filter($urlParam); // 去除数组中的空值

                foreach ($urlParam as $key => $val) {

                    $newUrlParam = explode('=', $val);

                    $param[$key][$newUrlParam[0]] = $newUrlParam[1];

                }

            } else {

                //(aaa = 123) = (aaa => 123)
                $urlParam = explode('=', $urlParam);

                $param[$urlParam[0]] = $urlParam[1];

            }
        }

        // 判断控制器和操作是否存在
        $controller = 'app\\controllers\\'. $controllerName . 'Controller';
        if (!class_exists($controller)) {
            exit($controller . '控制器不存在');
        }
        if (!method_exists($controller, $actionName)) {
            exit($actionName . '方法不存在');
        }

        // 如果控制器和操作名存在，则实例化控制器，因为控制器对象里面还会用到控制器名和操作名，所以实例化的时候把他们俩的名称也传进去。结合Controller基类一起看
        $dispatch = new $controller($controllerName, $actionName);

        // $dispatch保存控制器实例化后的对象，我们就可以调用它的方法，也可以像方法中传入参数，以下等同于：$dispatch->$actionName($param)
        call_user_func_array([$dispatch, $actionName], $param);
    }

    // 自动加载类
    public function loadClass($className)
    {
        // 获取核心文件
        $classMap = $this->classMap();

        if (isset($classMap[$className])) {
            // 包含内核文件
            $file = $classMap[$className];
        } else if (strpos($className, '\\') !== false) {
            // 包含应用（application目录）文件
            $file = APP_PATH . str_replace('\\', '/', $className) . '.php';
            if (!is_file($file)) {
                echo '<h1>'.$className."文件不存在</h1>";die;
            }
        } else {
            return;
        }

        include $file;

        // 这里可以加入判断，如果名为$className的类、接口或者性状不存在，则在调试模式下抛出错误
        if (!class_exists($className)) {
            $class = substr($className, strrpos($className, '\\')+1, strlen($className));
            echo '<h1><font color="red">'.$class."</font>类不存在</h1>";die;
        }

    }

    // 内核文件命名空间映射关系
    protected function classMap()
    {
        return [
            'corephp\base\Controller' => CORE_PATH . '/base/Controller.php',
            'corephp\base\Model' => CORE_PATH . '/base/Model.php',
            'corephp\base\View' => CORE_PATH . '/base/View.php',
            'corephp\db\Db' => CORE_PATH . '/db/Db.php',
            'corephp\db\Sql' => CORE_PATH . '/db/Sql.php',
        ];
    }

}