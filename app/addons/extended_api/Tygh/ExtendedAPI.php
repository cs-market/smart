<?php

namespace Tygh;

class ExtendedAPI extends Api
{
    protected $called_version = '5.0';

    /**
     * Creates API instance
     *
     * @param  array $formats
     */
    public function __construct($formats = array('json', 'text', 'form'))
    {
        parent::__construct($formats);
        $this->authenticate();
    }

    /**
     * Gets a fully qualified class name for entity.
     *
     * @param string $entity_name   Entity name
     * @param string $version       Required version
     *
     * @return string|false Returns a fully qualified class name on success, otherwise false.
     */
    protected function getEntityClass($entity_name, $version)
    {
        $version_namespace = '';

        if ($version !== self::CURRENT_VERSION) {
            $version_namespace = 'v' . str_replace('.', '', $version) . '\\';
        }

        $result = '\\Tygh\\Api\\Entities\\' . $version_namespace . fn_camelize($entity_name);
        if (!class_exists($result)) {
            $version_arr = explode('.', $version);
            if ($version_arr[0] > 2) {
                $version_arr[0] -= 1;
                $result = $this->getEntityClass($entity_name, implode('.', $version_arr));
            }
        }
        if (!class_exists($result) && isset($this->versions_fallback[$version])) {
            $result = $this->getEntityClass($entity_name, $this->versions_fallback[$version]);
        }

        return $result && class_exists($result) ? $result : false;
    }
}
