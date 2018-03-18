<?php
namespace corephp\base;

/**
 * 视图基类
 */
class View
{
    protected $variables = array();
    protected $_controller;
    protected $_action;
    protected $layout;

    function __construct($controller, $action, $layout = '')
    {
        // strtolower将字符串转化为小写
        $this->_controller = strtolower($controller);
        $this->_action = strtolower($action);
        if (!empty($layout)) $this->layout = $layout;
    }

    // 分配变量
    public function assign($key, $value = '')
    {
        if (is_array($key)) {
            foreach ($key as $k => $value) {
                $this->variables[$k] = $value;
            }
        } else {
            $this->variables[$key] = $value;
        }
    }

    // 渲染显示
    public function render($key = [])
    {

        if (!empty($key)) {
            foreach ($key as $k => $val) {
                $this->variables[$k] = $val;
            }
        }

        // extract 返回的结合数组中的内容导入到符号表变量中去
        extract($this->variables);



        // 加载视图
        $action = substr($this->_action, 6, strlen($this->_action));
        $actionUrl = APP_PATH . 'app/views/' . $this->_controller . '/' . $action . '.php';


        // 处理 公共布局
        if (!empty($this->layout)) {
            //判断视图文件是否存在
            if (is_file($actionUrl)) {
                $content = file_get_contents($actionUrl);
            } else {
                echo "<h1>无法找到" . $action . "视图文件</h1>";
            }
            $controllerLayout = APP_PATH . 'app/views/layout/' . $this->layout;
            //判断公共视图文件是否存在
            if (is_file($controllerLayout)) {
                include ($controllerLayout);
            } else {
                echo "<h1>无法找到".$this->layout."视图文件</h1>";
            }
        } else {
            //判断视图文件是否存在
            if (is_file($actionUrl)) {
                include ($actionUrl);
            } else {
                echo "<h1>无法找到" . $action . "视图文件</h1>";
            }
        }

    }

}