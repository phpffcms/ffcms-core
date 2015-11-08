<?php

namespace Ffcms\Core;


use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Object;
use Ffcms\Core\Helper\Type\Str;

class Properties
{
    protected $data;

    public $version = [
        'num' => '3.0.0alpha',
        'date' => '18.09.2015'
    ];

    /**
     * Load default configuration
     * @throws NativeException
     */
    public function __construct()
    {
        if ($this->load('default') === false) {
            throw new NativeException('Default configurations is not founded: /Private/Config/Default.php');
        }
    }

    /**
     * Load configurations from file in /Private/Config/
     * @param string $configName
     * @return bool
     */
    private function load($configName)
    {
        // check if always loaded
        if (Object::isArray($this->data) && array_key_exists($configName, $this->data)) {
            return true;
        }

        // try to load from file
        $configFile = ucfirst(Str::lowerCase($configName)) . '.php';
        if (File::exist('/Private/Config/' . $configFile)) {
            $this->data[$configName] = @include(root . '/Private/Config/' . $configFile);
            return true;
        }

        return false;
    }

    /**
     * Get config value by config key from configuration file
     * @param string $configKey
     * @param string $configFile
     * @param bool|false $parseType
     * @return mixed
     */
    public function get($configKey, $configFile = 'default', $parseType = false)
    {
        $this->load($configFile);
        // check if configs for this file is loaded
        if (!isset($this->data[$configFile])) {
            return false;
        }

        // check if config key is exist
        if (!isset($this->data[$configFile][$configKey])) {
            return false;
        }

        $response = $this->data[$configFile][$configKey];

        // try to convert config value by defined parse type
        $parseType = Str::lowerCase($parseType);
        switch ($parseType) {
            case 'int':
            case 'integer':
                $response = (int)$response;
                break;
            case 'bool':
            case 'boolean':
                $response = (bool)$response;
                break;
            case 'float':
                $response = (float)$response;
                break;
            case 'double':
                $response = (double)$response;
                break;
        }

        return $response;
    }

    /**
     * Get all configuration data of selected file
     * @param string $configFile
     * @return bool|array
     */
    public function getAll($configFile = 'default')
    {
        $this->load($configFile);
        if (!isset($this->data[$configFile])) {
            return false;
        }

        return $this->data[$configFile];
    }

    /**
     * Update configuration data based on key-value array of new data
     * @param string $configFile
     * @param array $newData
     * @return bool
     */
    public function updateConfig($configFile, array $newData)
    {
        $this->load($configFile);
        if (!isset($this->data[$configFile])) {
            return false;
        }

        $saveData = Arr::merge($this->data[$configFile], $newData);
        $saveData = '<?php return ' . App::$Security->var_export54($saveData) . ';';
        File::write('/Private/Config/' . ucfirst(Str::lowerCase($configFile)) . '.php', $saveData);
        return true;
    }


}