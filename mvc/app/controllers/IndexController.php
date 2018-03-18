<?php

namespace app\controllers;

use app\Models\GoodsModel as Goods;

/**
* 测试
*/
class IndexController extends \corephp\base\Controller
{
	// public $layout = 'layout.php';

	public function actionIndex()
	{
		// echo 2;die;
		// $goods = new Goods;
		// $data['name'] = 'aaa';
		// $data['pwd'] = 'bbb';
		// // $goods->delete(1);
		// // $data = $goods->where(['id = ?'], [3])->fetchAll();
		// $goods->add($data);
		// var_dump($data);die;
		// $this->assign('a','b');
		$this->render(['b'=> 1]);
	}
}