<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

use RuntimeException;

/**
 * Class ReportSuccessHeader
 * @package App
 */
class ReportHeader
{
    /**
     * @var array|string[]
     */
    private $headerMap = [
        'login' => 'USR_LOGIN',
        'first_name' => 'USR_FIRST_NAME',
        'last_name' => 'USR_LAST_NAME',
        'email' => 'USR_EMAIL',
        'phone' => 'USR_MOBILE',
        'chief_email' => 'MANAGER_EMAIL',
        'status' => 'USR_UDF_USER_FIRED',
        'groups.region' => 'REGION_NAME',
        'groups.city' => 'CITY_NAME',
        'groups.role' => 'ROLE',
        'groups.position' => 'POSITION_NAME',
        'groups.team' => 'TEAM_NAME',
        'groups.department' => 'DEPARTAMENT_NAME',
        'groups.assignment' => 'ASSIGNMENT_NAME',
    ];

    /**
     * @var array
     */
    private $formMap = [];
    /**
     * @var array
     */
    private $withoutFields = [];

    /**
     * ReportSuccessHeader constructor.
     * @param array $headerMap
     * @param array $options
     */
    public function __construct(array $headerMap = [], array $options = [])
    {
        if($headerMap) {
            $this->headerMap = array_merge($this->headerMap, $headerMap);
        }

        if(isset($options['withoutFields']) && is_array($options['withoutFields'])) {
            $this->withoutFields = $options['withoutFields'];
        }
    }

    /**
     * @param array $forms
     * @return $this
     */
    public function addForms(array $forms): self
    {
        $self = clone $this;
        $data = [];
        foreach ($forms as $id => $name) {
            $key = sprintf('forms.%s', $id);
            $data[$key] = $name;
        }
        $self->formMap = array_merge($this->formMap, $data);

        return $self;
    }

    /**
     * @return array
     */
    public function headers(): array
    {
        if($this->withoutFields) {
            $headers = $this->headerMap;
            $forms = $this->formMap;
            foreach ($this->withoutFields as $field) {
                unset($headers[$field]);
                unset($forms[$field]);
            }

            return array_merge(array_values($headers), array_values($forms));
        }

        return array_merge(array_values($this->headerMap), array_values($this->formMap));
    }

    /**
     * @param string $header
     * @return string
     */
    public function getFieldByHeader(string $header): string
    {
        $flip = array_flip(array_merge($this->headerMap, $this->formMap));

        if(!isset($flip[$header])) {
            throw new RuntimeException(sprintf('No map field for %s key', $header));
        }

        return $flip[$header];
    }

    /**
     * @param string $field
     * @return string
     */
    public function getHeaderByField(string $field): string
    {
        $headers = array_merge($this->headerMap, $this->formMap);

        if(!isset($headers[$field])) {
            throw new RuntimeException(sprintf('No map header for %s field', $field));
        }

        return $headers[$field];
    }
}