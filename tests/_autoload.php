<?php

/**
 * Setup autoloading
 */
function DetainTest_Autoloader($class)
{
    $class = ltrim($class, '\\');

    if (!preg_match('#^(Detain(Test)?|Zend|PHPUnit)(\\\\|_)#', $class)) {
        return false;
    }

    // $segments = explode('\\', $class); // preg_split('#\\\\|_#', $class);//
    $segments = preg_split('#[\\\\_]#', $class); // preg_split('#\\\\|_#', $class);//
    $ns       = array_shift($segments);

    switch ($ns) {
        case 'Detain':
            $file = dirname(__DIR__) . '/src/Detain/';
            break;
        case 'DetainTest':
            $file = __DIR__ . '/DetainTest/';
            break;
        default:
            $file = false;
            break;
    }

    if ($file) {
        $file .= implode('/', $segments) . '.php';
        if (file_exists($file)) {
            return include_once $file;
        }
    }

    $segments = explode('_', $class);
    $ns       = array_shift($segments);

    switch ($ns) {
        case 'Detain':
            $file = dirname(__DIR__) . '/src/Detain/';
            break;
        case 'Zend':
            $file = 'Zend/';
            break;
        default:
            return false;
    }
    $file .= implode('/', $segments) . '.php';

//    if (file_exists($file)) {
        return include_once $file;
//    }

    return false;
}

spl_autoload_register('DetainTest_Autoloader', true, true);
