<?php

namespace Tygh\Addons\Telegram;

use Tygh\Addons\Telegram\Routes\ARoute;

class RenderManager {
    private $auth;
    private $area = 'C';
    private $chat_id = 0;

    public function initRender($auth = [], $area = 'C', $chat_id = 0) {
        $this->auth = $auth;
        $this->area = $area;
        $this->chat_id = $chat_id;
        return $this;
    }

    public function getEntityFromPath($resource_name)
    {
        $result = array(
            "name" => "",
            "id" => "",
            "params" => [],
        );
        $extra_params = [];

        $resource_name = preg_replace("/\/$/", "", $resource_name);

        if (strpos($resource_name, '/') === 0) {
            $resource_name = substr($resource_name,1);
            if (strpos($resource_name, ' ') !== false) {
                $extra_params = explode(' ', $resource_name);
                $resource_name = array_shift($extra_params);
            }
        } else {
            $resource_name = fn_get_storage_data('telegram_last_command_'.$this->chat_id);
        }

        $parsed_resource = parse_url($resource_name);


        if (!empty($parsed_resource['query'])) parse_str($parsed_resource['query'], $result['params']);
        $result['params'] = fn_array_merge($result['params'], $extra_params);

        $resource_name = explode("/", $parsed_resource['path']);

        if (!empty($resource_name[0])) {
            $result['name'] = array_shift($resource_name);

            if (!empty($resource_name[0])) {
                $result['id'] = array_shift($resource_name);
            }

            if (!empty($resource_name[0])) {
                $result['child_entity'] = implode("/", $resource_name);
            }
        }

        // redirect to auth
        if ($result['name'] == 'start' && !empty(reset($result['params'])) && strlen(reset($result['params'])) == 32) {
            $result['id'] = reset($result['params']);
            $result['name'] = 'auth';
        }

        return $result;
    }

    protected function getObjectByEntity($entity_properties)
    {
        $class_name = '\\Tygh\\Addons\\Telegram\\Routes\\' . fn_camelize($entity_properties['name']);

        return class_exists($class_name) === false ? null : new $class_name($this->auth, $this->area, $this->chat_id);
    }

    protected function getResponseFromEntity($entity_properties, $context)
    {
        $response = null;

        $entity = $this->getObjectByEntity($entity_properties);
        if ($entity !== null && $this->checkAccess($entity, $entity_properties, $context)) {
            $response = $entity->render($entity_properties['id'], $entity_properties['params'], $context);
        }

        return $response;
    }

    public function checkAccess($entity, $entity_properties, $context) {
        $can_access = false;

        if ($entity instanceof ARoute) {
            $can_access = $entity->isAccessable($entity_properties['id'], $entity_properties['params'], $context);
        }

        fn_set_hook('telegram_check_access', $this, $entity, $method_name, $can_access);

        return $can_access;
    }

    public function renderLocation($resource, $context) {
        $response = null;

        if ($resource) {
            $entity_properties = $this->getEntityFromPath($resource);

            $response = $this->getResponseFromEntity($entity_properties, $context);
        }

        return $response;
    }
}
