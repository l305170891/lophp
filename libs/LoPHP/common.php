<?php

//类自动加载
function autoloader($className){

    if (substr($className, -10) == 'Controller'){
        $classNamePre = substr($className, 0, -10);
		$file = APP_PATH . "controller/{$classNamePre}.controller.php";

        if (is_file($file)){
            include_once($file);
        } else {
            die("not find file {$className}.php");
        }
    }

    if (substr($className, 0, 4) == 'soap'){
        $classNamePre = substr($className, 0, -10);
        if (is_file(APP_PATH . "libs/nusoap/nusoap.php")) {
            include_once(APP_PATH . "libs/nusoap/nusoap.php");
        } else {
            die("not find file nusoap.php");
        }
    }

    if (strtolower(substr($className, 0, 4)) == 'twig'){
        $classNamePre = substr($className, 0, -10);
        if (is_file(APP_PATH . "libs/twig/Autoloader.php")) {
            include_once(APP_PATH . "libs/twig/Autoloader.php");
        } else {
            die("not find file twig Autoloader.php");
        }
    }

    spl_autoload_extensions('.controller.php,.php');
    spl_autoload($className);
}




function shutdownfn(){
    //流水日志
    if (isset($_SERVER['REQUEST_URI']))
    {
        $url = $_SERVER['REQUEST_URI'];
    }
    else
    {
        $url = '';
        isset($_SERVER['SCRIPT_NAME']) && $url .= $_SERVER['SCRIPT_NAME'];
        isset($_SERVER['PATH_INFO']) && $url .= $_SERVER['PATH_INFO'];
        isset($_SERVER['QUERY_STRING']) && $url .= '?' . $_SERVER['QUERY_STRING'];
    }

    Logs::info($_SERVER['REMOTE_ADDR'] . ', ' . $url . " \r\n".
            "ACCOUNT:" . Session::account() . " \r\n".
            "REQUEST:" . print_r( $_REQUEST, true));
    Logs::flush();
}