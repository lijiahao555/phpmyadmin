<?php

namespace corephp\base;

/**
* 公共控制器
*/
class Controller
{
    protected $_controller;
    protected $_action;
    protected $_view;
    public $layout;

    // 构造函数，初始化属性，并实例化对应模型
    public function __construct($controller, $action)
    {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_view = $this->layout ? new View($controller, $action, $this->layout) : new View($controller, $action);
    }

    // 分配变量
    public function assign($key, $value = '')
    {
    	if (is_array($key)) {
            $this->_view->assign($key);
        } else {
            $this->_view->assign($key, $value);
        }
    }

    // 渲染视图
    public function render($key = [])
    {
        if (empty($key)) {
            $this->_view->render();
        } else {
            $this->_view->render($key);
        }
    }
}