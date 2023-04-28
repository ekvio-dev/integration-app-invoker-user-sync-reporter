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
    protected $attributesMap = ['source' => 'SOURCE'];

    /**
     * @var array
     */
    protected $formMap = [];
    /**
     * @var array
     */
    protected $withoutFields = [];

    /**
     * ReportSuccessHeader constructor.
     * @param array $attributesMap
     * @param array $options
     */
    public function __construct(array $attributesMap, array $options = [])
    {
        foreach ($attributesMap as $key => $value) {
            if(is_array($value)) {
                foreach ($value as $innerKey => $innerValue) {
                    $this->attributesMap[sprintf('%s.%s', $key, $innerKey)] = $innerValue;
                }
                continue;
            }

            $this->attributesMap[$key] = $value;
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
            $headers = $this->attributesMap;
            $forms = $this->formMap;
            foreach ($this->withoutFields as $field) {
                unset($headers[$field]);
                unset($forms[$field]);
            }

            return array_merge(array_values($headers), array_values($forms));
        }

        return array_merge(array_values($this->attributesMap), array_values($this->formMap));
    }

    /**
     * @param string $header
     * @return string
     */
    public function getFieldByHeader(string $header): string
    {
        $flip = array_flip(array_merge($this->attributesMap, $this->formMap));

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
        $headers = array_merge($this->attributesMap, $this->formMap);

        if(!isset($headers[$field])) {
            throw new RuntimeException(sprintf('No map header for %s field', $field));
        }

        return $headers[$field];
    }
}