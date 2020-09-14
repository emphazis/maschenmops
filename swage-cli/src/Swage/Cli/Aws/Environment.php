<?php 

namespace Swage\Cli\Aws;

use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;

class Environment {

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $variables;

    public static function fromYaml(string $pathToYaml): Environment 
    {
        $config = Yaml::parse($pathToYaml);
 
        return new Environment(new Collection($config));
    }

    public static function fromArray(array $variables): Environment 
    {
        return new Environment($variables);
    }

    public function __construct($variables) 
    {
        if (! $variables instanceof Collection) {
            $variables = new Collection($variables);
        }

        $this->variables = $variables;
    }

    public function getCollection(): Collection 
    {
        return $this->variables;
    }

    public function toArray() 
    {
        return $this->variables->toArray();
    }
}
