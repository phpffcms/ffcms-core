<?php

namespace Ffcms\Core\Debug\Collectors;


use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class FfcmsInfoCollector extends DataCollector implements Renderable
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'ffcms';
    }

    /**
     * @return array
     */
    public function collect()
    {
        return [
            'version' => \Extend\Version::VERSION,
            'release_date' => \Extend\Version::DATE
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return [
            "ffcms_version" => [
                "icon" => "shield",
                "tooltip" => "FFCMS Version",
                "map" => "ffcms.version",
                "default" => ""
            ]
        ];
    }
}