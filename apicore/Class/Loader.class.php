<?php
/**
 * 类加载
 * @author yanghao
 * @date 17-03-31
 */
class Class_Loader
{

    private $rootPath = '';
    private $pluginPath = array();
    private $hlwPath = array();
    private $appPath = array();
    private $corePath = array();
    private $thriftPath = array();
    private $sdkPath = array();
    private $pathCRC = array();

    public function __construct()
    {
        $this->rootPath = realpath(__DIR__ . '/../../../');

        $this->appendPath('hlw', array($this->rootPath . '/hlw_phpframe/hlwcore'));

        $this->appendPath('plugin', array($this->rootPath . '/hlw_phpframe/plugins'));

        $this->appendPath('core', array($this->rootPath . '/hlw_php/apicore'));

        $this->appendPath('thrift', array($this->rootPath . '/hlw_php/lib'));
    }

    public function appendPath($target, $path)
    {
        if ($path == NULL) {
            return;
        }

        $pathName = "{$target}Path";
        if (!isset($this->$pathName)) {
            return;
        }

        if (!is_array($path)) {
            $path = array($path);
        }

        foreach ($path as $_path) {
            $_path = rtrim(realpath($_path), "/\\ ");
            if ($_path == '') {
                continue;
            }

            if (in_array($_path, $this->$pathName)) {
                continue;
            }

            array_push($this->$pathName, $_path);
        }
    }

    public function appendSdk($sdk_lib)
    {
        if ($sdk_lib == NULL) {
            return;
        }

        if (!is_array($sdk_lib)) {
            $sdk_lib = array($sdk_lib);
        }

        foreach ($sdk_lib as $lib) {
            $this->appendPath('sdk', "{$this->rootPath}/{$lib}/php");
        }
    }

    public function registerLoader()
    {
        spl_autoload_register(array($this, 'loadClass'), TRUE);
    }

    /**
     * load class file
     * @param string $class
     */
    public function loadClass($class)
    {
        if ($class{0} == 'S' && $this->loadFile('plugin', "{$class}.class.php", $class)) {
            return;
        }
        if ($this->loadFile('hlw', str_replace('_', '/', substr($class, 4)) . '.class.php', $class)) {
            return;
        }

        if ($this->loadFile('app', str_replace('_', '/', $class) . '.class.php', $class)) {
            return;
        }
        $prefix = str_replace(
                array('\\', '_'), DIRECTORY_SEPARATOR, ltrim($class, '\\_ '));

        // thrift lib
        if (strpos(ltrim($class, '\\_ '), 'Thrift\\') !== FALSE && $this->loadFile('thrift', $prefix . '.php', $class)) {
            return;
        }

        // apicore
        if ($this->loadFile('core', $prefix . '.class.php', $class)) {
            return;
        }

        if ($pos = strrpos($class, '\\')) {
            switch (TRUE) {
                // service
                case preg_match('#(.+)(if|client|processor|rest)$#i', $class, $ns):
                case preg_match('#(.+)_[a-z0-9]+_(args|result)$#i', $class, $ns):
                    $file = str_replace('\\', '/', $ns[1]) . '.php';
                    break;
                // type
                default:
                    $dir = substr($class, 0, $pos);
                    $file = str_replace('\\', '/', $dir) . '/Types.php';
                    break;
            }

            if ($this->loadFile('sdk', $file, $class)) {
                return;
            }
        }
    }

    /**
     * load file
     * 
     * @param string $target
     * @param string $file
     * @param string $class
     * @return bool
     */
    private function loadFile($target, $file, $class) 
    {
        $pathName = "{$target}Path";
        if (!isset($this->$pathName)) {
            return FALSE;
        }

        foreach ($this->$pathName as $dir) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (!file_exists($path)) {
                continue;
            }

            $crc = crc32($path);
            if (in_array($crc, $this->pathCRC)) {
                continue;
            }

            include $path;

            $this->pathCRC[] = $crc;

            if (class_exists($class, FALSE)) {
                return TRUE;
            }

            if (interface_exists($class, FALSE)) {
                return TRUE;
            }
        }

        return FALSE;
    }

}
