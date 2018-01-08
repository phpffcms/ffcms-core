<?php

namespace Ffcms\Core;

use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class Properties. Provide methods to work with ffcms configs.
 * @package Ffcms\Core
 */
class Properties
{
    protected $data;

    /**
     * Load default configuration
     * @throws NativeException
     */
    public function __construct()
    {
        if (!$this->load('default')) {
            throw new NativeException('Default configurations is not founded: /Private/Config/Default.php');
        }
    }

    /**
     * Load configurations from file in /Private/Config/
     * @param string $configName
     * @param bool $overload
     * @return bool
     */
    private function load(string $configName, $overload = false): bool
    {
        // check if always loaded
        if (Any::isArray($this->data) && array_key_exists($configName, $this->data) && !$overload) {
            return true;
        }

        // try to load from file
        $configFile = ucfirst(Str::lowerCase($configName)) . '.php';
        if (File::exist('/Private/Config/' . $configFile)) {
            $this->data[$configName] = File::inc('/Private/Config/' . $configFile, true);
            return true;
        }

        return false;
    }

    /**
     * Get config value by config key from configuration file
     * @param string $configKey
     * @param string $configFile
     * @param string|null $parseType
     * @return mixed
     */
    public function get(string $configKey, string $configFile = 'default', ?string $parseType = null)
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
     * @return array|null
     */
    public function getAll($configFile = 'default'): ?array
    {
        $this->load($configFile);
        if (!Any::isArray($this->data) || !array_key_exists($configFile, $this->data)) {
            return null;
        }

        return $this->data[$configFile];
    }

    /**
     * Update configuration data based on key-value array of new data. Do not pass multi-level array on new position without existing keys
     * @param string $configFile
     * @param array $newData
     * @param bool $mergeDeep
     * @return bool
     */
    public function updateConfig(string $configFile, array $newData, ?bool $mergeDeep = false): bool
    {
        $this->load($configFile);
        if (!isset($this->data[$configFile])) {
            return false;
        }

        $oldData = $this->data[$configFile];
        $saveData = ($mergeDeep ? Arr::mergeRecursive($oldData, $newData) : Arr::merge($oldData, $newData));
        return $this->writeConfig($configFile, $saveData);
    }

    /**
     * Write configurations data from array to cfg file
     * @param string $configFile
     * @param array $data
     * @return bool
     */
    public function writeConfig(string $configFile, array $data): bool
    {
        $path = '/Private/Config/' . ucfirst(Str::lowerCase($configFile)) . '.php';
        if (!File::exist($path) || !File::writable($path)) {
            return false;
        }

        $saveData = '<?php return ' . Arr::exportVar($data) . ';';
        File::write($path, $saveData);
        // overload config values if changed
        $this->load($configFile, true);
        return true;
    }
}
