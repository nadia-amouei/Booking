<?php

namespace Symfony\Config\ApiPlatform;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MappingConfig 
{
    private $imports;
    private $paths;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function imports(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['imports'] = true;
        $this->imports = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function paths(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['paths'] = true;
        $this->paths = $value;

        return $this;
    }

    public function __construct(array $value = [])
    {
        if (array_key_exists('imports', $value)) {
            $this->_usedProperties['imports'] = true;
            $this->imports = $value['imports'];
            unset($value['imports']);
        }

        if (array_key_exists('paths', $value)) {
            $this->_usedProperties['paths'] = true;
            $this->paths = $value['paths'];
            unset($value['paths']);
        }

        if ([] !== $value) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($value)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['imports'])) {
            $output['imports'] = $this->imports;
        }
        if (isset($this->_usedProperties['paths'])) {
            $output['paths'] = $this->paths;
        }

        return $output;
    }

}
