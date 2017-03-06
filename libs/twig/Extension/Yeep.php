<?php

/**
 * This file is part of Twig.
 *
 * @author jeff
 * @package Twig
 * @subpackage Twig-extensions
 */
class Twig_Extension_Yeep extends Twig_Extension {

    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters() {
        $filters = array(
            'truncate' => new Twig_Filter_Function('twig_truncate_filter', array('needs_environment' => true)),
            'wordwrap' => new Twig_Filter_Function('twig_wordwrap_filter', array('needs_environment' => true)),
            'thum' => new Twig_Filter_Function('twig_thum_filter', array('needs_environment' => true)),
            'phpfuns' => new Twig_Filter_Function('twig_phpfuns_filter', array('needs_environment' => true)),
        );

        if (version_compare(Twig_Environment::VERSION, '1.5.0-DEV', '<')) {
            $filters['nl2br'] = new Twig_Filter_Function('twig_nl2br_filter', array('pre_escape' => 'html', 'is_safe' => array('html')));
        }

        return $filters;
    }

    public function getFunctions() {
        $functions = array(
            'url' => new Twig_Function_Function('twig_url_function', array('needs_environment' => true)),
        );

        return $functions;
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName() {
        return 'Yeep';
    }

}

function twig_nl2br_filter($value, $sep = '<br />') {
    return str_replace("\n", $sep . "\n", $value);
}

if (function_exists('mb_get_info')) {

    function twig_truncate_filter(Twig_Environment $env, $value, $length = 30, $preserve = false, $separator = '...') {
        if (mb_strlen($value, $env->getCharset()) > $length) {
            if ($preserve) {
                if (false !== ($breakpoint = mb_strpos($value, ' ', $length, $env->getCharset()))) {
                    $length = $breakpoint;
                }
            }

            return mb_substr($value, 0, $length, $env->getCharset()) . $separator;
        }

        return $value;
    }

    function twig_wordwrap_filter(Twig_Environment $env, $value, $length = 80, $separator = "\n", $preserve = false) {
        $sentences = array();

        $previous = mb_regex_encoding();
        mb_regex_encoding($env->getCharset());

        $pieces = mb_split($separator, $value);
        mb_regex_encoding($previous);

        foreach ($pieces as $piece) {
            while (!$preserve && mb_strlen($piece, $env->getCharset()) > $length) {
                $sentences[] = mb_substr($piece, 0, $length, $env->getCharset());
                $piece = mb_substr($piece, $length, 2048, $env->getCharset());
            }

            $sentences[] = $piece;
        }

        return implode($separator, $sentences);
    }

} else {

    function twig_truncate_filter(Twig_Environment $env, $value, $length = 30, $preserve = false, $separator = '...') {
        if (strlen($value) > $length) {
            if ($preserve) {
                if (false !== ($breakpoint = strpos($value, ' ', $length))) {
                    $length = $breakpoint;
                }
            }

            return substr($value, 0, $length) . $separator;
        }

        return $value;
    }

    function twig_wordwrap_filter(Twig_Environment $env, $value, $length = 80, $separator = "\n", $preserve = false) {
        return wordwrap($value, $length, $separator, !$preserve);
    }

}

/**
 * 缩略图生成
 * @param Twig_Environment $env
 * @param string $value
 * @param int $width
 * @param int $height
 * @param boolean $isCrop
 * @return string
 */
function twig_thum_filter(Twig_Environment $env, $value, $width, $height, $isCrop = true) {
    $appGlobals = $env->getGlobals();
    $appConfig = $appGlobals['appConfig'];
    $webroot = $appConfig['_webroot'];
    $approot = $appConfig['_appRoot'];
    $_uploadFilePath = $appConfig['_uploadFilePath'];
    return '';
}

function img2thumb($src_img, $dst_img, $width = 75, $height = 75, $cut = 0, $proportion = 0) {
    if (!is_file($src_img)) {
        return false;
    }
    $ot = pathinfo($dst_img, PATHINFO_EXTENSION);
    $ot = strtolower($ot);
    $allowext = array('jpeg', 'jpg', 'gif', 'png');
    if (!in_array($ot, $allowext))
        return false;
    $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
    $srcinfo = getimagesize($src_img);
    $src_w = $srcinfo[0];
    $src_h = $srcinfo[1];
    $type = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
    $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);

    $dst_h = $height;
    $dst_w = $width;
    $x = $y = 0;

    /**
     * 缩略图不超过源图尺寸（前提是宽或高只有一个）
     */
    if (($width > $src_w && $height > $src_h) || ($height > $src_h && $width == 0) || ($width > $src_w && $height == 0)) {
        $proportion = 1;
    }
    if ($width > $src_w) {
        $dst_w = $width = $src_w;
    }
    if ($height > $src_h) {
        $dst_h = $height = $src_h;
    }

    if (!$width && !$height && !$proportion) {
        return false;
    }
    if (!$proportion) {
        if ($cut == 0) {
            if ($dst_w && $dst_h) {
                if ($dst_w / $src_w > $dst_h / $src_h) {
                    $dst_w = $src_w * ($dst_h / $src_h);
                    $x = 0 - ($dst_w - $width) / 2;
                } else {
                    $dst_h = $src_h * ($dst_w / $src_w);
                    $y = 0 - ($dst_h - $height) / 2;
                }
            } else if ($dst_w xor $dst_h) {
                if ($dst_w && !$dst_h) {  //有宽无高
                    $propor = $dst_w / $src_w;
                    $height = $dst_h = $src_h * $propor;
                } else if (!$dst_w && $dst_h) {  //有高无宽
                    $propor = $dst_h / $src_h;
                    $width = $dst_w = $src_w * $propor;
                }
            }
        } else {
            if (!$dst_h) {  //裁剪时无高
                $height = $dst_h = $dst_w;
            }
            if (!$dst_w) {  //裁剪时无宽
                $width = $dst_w = $dst_h;
            }
            $propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
            $dst_w = (int) round($src_w * $propor);
            $dst_h = (int) round($src_h * $propor);
            $x = ($width - $dst_w) / 2;
            $y = ($height - $dst_h) / 2;
        }
    } else {
        $proportion = min($proportion, 1);
        $height = $dst_h = $src_h * $proportion;
        $width = $dst_w = $src_w * $proportion;
    }

    $src = $createfun($src_img);
    $dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);

    if (function_exists('imagecopyresampled')) {
        imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    } else {
        imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    }
    $otfunc($dst, $dst_img);
    imagedestroy($dst);
    imagedestroy($src);
    return true;
}

/**
 *
 * @param string $action
 * @param string $controller
 * @param array $param
 * @return string
 */
function twig_url_function(Twig_Environment $env, $action = null, $controller = null, $param = null) {
    $_url = '';
    $appGlobals = $env->getGlobals();
    $appConfig = $appGlobals['appConfig'];
    $webroot = $appConfig['_webroot'];
    if ($action == null)
        $action = $appConfig['_action'];
    if ($controller == null)
        $controller = $appConfig['_controller'];
    if (!isset($param[MODULE_PARAM_KEY]) && $appConfig['_module'] != '')
    {
        $param[MODULE_PARAM_KEY] = $appConfig['_module'];
    }
    if ($param != null) {
        foreach ($param as $key => $value) {
            $_url .= $key . "-" . $value . "/";
        }
    }
    $_url = trim($_url, '/');
    return $webroot . $controller . "/" . $action . "/" . $_url;
}

function twig_phpfuns_filter(Twig_Environment $env, $values, $funname ){
    $return = null;
    $numargs = func_num_args();
    $arg_list = func_get_args();
    $args = $arg_list[1] ;
    if($numargs>3){
        for ($i = 3;$i<$numargs;$i++){
            $args .= ",\"".$arg_list[$i]."\"";
        }
    }
    eval('$return = @call_user_func("'.$arg_list[2].'", '.$args.');');
    return $return;

}