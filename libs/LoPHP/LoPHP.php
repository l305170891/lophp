<?php

//记录开始运行时间
$GLOBALS['_beginTime'] = microtime(TRUE);

//是否调试模式
defined('APP_DEBUG') 	or define('APP_DEBUG',false);


//定义系统配置
defined('APP_PATH') 	or define('APP_PATH',  dirname($_SERVER['SCRIPT_FILENAME']).'/');
defined('APP_LIBS') 	or define('APP_LIBS',  	APP_PATH . 'libs/');
defined('APP_LOPHP') 	or define('APP_LOPHP',  APP_PATH . 'libs/LoPHP/');
defined('APP_CONFIG') 	or define('APP_CONFIG',	APP_PATH . 'config/');
defined('APP_MODEL') 	or define('APP_MODEL',	APP_PATH . 'model/');
defined('APP_VIEW') 	or define('APP_VIEW',	APP_PATH . 'view/');
defined('APP_CTRL') 	or define('APP_CTRL',	APP_PATH . 'controller/');
defined('APP_PUBLIC') 	or define('APP_PUBLIC',	APP_PATH . 'public/');
defined('APP_LOG') 		or define('APP_LOG',	APP_PATH . 'log/');


//扩展方法
require_once(APP_LOPHP . 'function.php');
require_once(APP_LOPHP . 'common.php');



//普通设置
require_once(APP_CONFIG . 'config.php');



// 设置include包含文件所在的所有目录
$include_path = get_include_path(); // 原基目录
$include_path .= PATH_SEPARATOR . APP_ROOT . "model/"; 
set_include_path($include_path);

spl_autoload_register('autoloader');
register_shutdown_function('shutdownfn');



//参数传递形式
//_=area/list/city_chengddu/code_028/
$controller = $action = "index";
$args = @explode('/', (string) $_SERVER['PATH_INFO']);
array_shift($args);
if (!empty($args[0]))
    $controller = array_shift($args);
if (!empty($args[0])) {
    $action = array_shift($args);
    foreach ($args as $arg) {
        $parms_index = stripos($arg, '-');
        if ($parms_index) {
            $_key = substr($arg, 0, $parms_index);
            $_val = substr($arg, $parms_index + 1);
            $_GET[$_key] = $_val;
        }
    }
}


if (empty($controller)){
    $controller = isset($_GET ['c']) ? (string) $_GET ['c'] : $controller; // 获取控制器,默认index;
    $action = isset($_GET ['a']) ? (string) $_GET ['a'] : $action; // 方法名称，默认index;
}


$controllerConfig = array(
    'session.autostart' => false,
    'encoding' => 'utf-8',
    'contentType' => 'text/html',
    '_action' => $action,
    '_controller' => $controller,
    '_module' => $module,
    '_webroot' => WEB_ROOT,
    '_uploadFilePath' => Config::getRecordingPath(),
    '_appRoot' => APP,
);

$tempdata = array();
$data = array(
    '_action' => $action,
    '_controller' => $controller,
    '_module' => $module,
    '_defaultModuleUrlParams' => array(MODULE_PARAM_KEY => ''),
    '_time' => time(),
);
if ($module != '')
{
    $data['_moduleStaticUrl'] = MODULE_STATIC_ROOT;
}


$forward = array($action, $controller, $module);
while ($forward)
{
    list($action, $controller, $module) = $forward;

    $controllerName = "{$controller}Controller";
    $app = new $controllerName();
    $tempdata = $app->config($controllerConfig)->action($action, $controller, $module)->execute();
    if ($tempdata)
        $data = array_merge($data, $tempdata);
    $forward = $app->getForward();
}
$tpl = $app->getTemplate();

$encoding = $app->getEncoding() ? $app->getEncoding() : 'utf-8';
$contentType = $app->getContentType() ? $app->getContentType() : 'text/html';


header("Content-Type:$contentType;charset=$encoding"); // 设置系统的输出字符为utf-8
if ($tpl){
    //设置模板引擎
    Twig_Autoloader::register();
    $loader = new Twig_Loader_Filesystem($APP_VIEW);
    $twig = new Twig_Environment($loader, array(
        'cache' => false,
        'debug' => true
    ));
    $twig->addExtension(new Twig_Extension_Debug());
    $twig->addGlobal('appConfig', $controllerConfig);
    //渲染模板
    echo $twig->render($tpl, (array) $data);
}
else
{
    print_r($data);
}







