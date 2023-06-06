<?php

namespace Tygh;

use Tygh\Api\ExtendedResponse as Response;

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

    protected function exec($entity, $entity_properties) {
        $response = null;

        $accept_type = $this->request->getAcceptType();
        $http_method = $this->request->getMethod();
        $method_name = $this->getMethodName($http_method);

        $request_data = $this->request->getData();

        if ($this->request->getError()) {
            $response = new Response(Response::STATUS_BAD_REQUEST, $this->request->getError(), $accept_type);
        } elseif (!$method_name) {
            $response = new Response(Response::STATUS_METHOD_NOT_ALLOWED);
        } elseif (isset($this->fake_entities[$entity_properties['name']])) {
            $fake_entity = $this->fake_entities[$entity_properties['name']];
            if (is_array($fake_entity) && !empty($fake_entity[$method_name]) && method_exists($this, $fake_entity[$method_name])) {
                $result = $this->{$fake_entity[$method_name]}();
                $response = new Response($result['status'], $result['data']);
            } elseif (is_string($fake_entity) && method_exists($this, $fake_entity)) {
                $result = $this->$fake_entity();
                $response = new Response($result['status'], $result['data']);
            } else {
                $response = new Response(Response::STATUS_FORBIDDEN);
            }
        } elseif (!$this->checkAccess($entity, $method_name)) {
            $response = new Response(Response::STATUS_FORBIDDEN);
        } else {
            $reflection_method = new \ReflectionMethod($entity, $method_name);
            $accepted_params = $reflection_method->getParameters();
            $call_params = array();

            if (fn_allowed_for('ULTIMATE')) {
                if ($http_method == 'POST' || $http_method == 'PUT') {
                    fn_ult_parse_api_request($entity_properties['name'], $request_data);
                }
            }

            foreach ($accepted_params as $param) {
                $param_name = $param->getName();

                if ($param_name == 'id') {
                    $call_params[] = !empty($entity_properties['id']) ? $entity_properties['id'] : '';

                    if (empty($entity_properties['id']) && !$param->isOptional()) {
                        $response = new Response(Response::STATUS_METHOD_NOT_ALLOWED, __('api_need_id'), $accept_type);
                    }
                }

                if ($param_name == 'params') {
                    $call_params[] = $request_data;

                    if (empty($request_data) && !$param->isOptional()) {
                        $response = new Response(Response::STATUS_METHOD_NOT_ALLOWED, __('api_need_params'), $accept_type);
                    }
                }
            }

            if ($http_method != 'POST' || empty($entity_properties['id'])) {
                if ($response == null) {
                    $controller_result = $reflection_method->invokeArgs($entity, $call_params);

                    if (!empty($controller_result['status'])) {
                        $data = isset($controller_result['data']) ? $controller_result['data'] : array();
                        $response = new Response($controller_result['status'], $data, $accept_type);

                    } else {
                        $response = new Response(Response::STATUS_INTERNAL_SERVER_ERROR);
                    }
                }
            } else {
                $response = new Response(Response::STATUS_METHOD_NOT_ALLOWED, __('api_not_need_id'), $accept_type);
            }
        }

        fn_set_hook('api_exec', $this, $entity, $entity_properties, $response);

        return $response;
    }
}
