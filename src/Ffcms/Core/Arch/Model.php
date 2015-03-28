<?php

namespace Ffcms\Core\Arch;

use \Core\Helper\Security;
use Core\Helper\String;

class Model {

    /**
    public function __set($param, $value)
    {
        if(!in_array($param, $this->reservedNames()))
            $this->properties[$param] = \App::$Security->purifier()->purify($value);
    }

    public function __get($param)
    {
        if(!in_array($param, $this->reservedNames()))
            return \App::$Security->purifier()->purify($this->properties[$param]);
        return null;
    }
    */

    /**
     * Set attribute labels for model variables
     * @return array
     */
    public function setLabels()
    {
        return [];
    }


    /**
     * Get label value by variable name
     * @param string $param
     * @return mixed
     */
    public final function getLabel($param)
    {
        $labels = $this->setLabels();
        return $labels[$param];
    }

    /**
     * Set model validation rules
     * @return array
     */
    public function setRules()
    {
        return [];
    }

    /**
     * Validate defined rules in app
     * @return bool
     */
    public final function validateRules()
    {
        $rules = $this->setRules();
        var_dump($rules);
    }


    /**
     * Export model values for safe-using in HTML pages.
     * @return $this
     */
    public final function export()
    {
        $properties = get_object_vars($this);
        foreach($properties as $var => $value) {
            if(String::startsWith('_', $var)) // ignore $_var
                continue;
            $this->$var = \App::$Security->purifier()->purify($value);
        }
        return $this;
    }

    protected final function reservedNames()
    {
        return [
            'labels',
        ];
    }

}