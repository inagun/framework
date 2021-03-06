<?php
namespace Suricate;

/**
 * Validator
 * Inspired from Kieron Wilson PHP Validator
 *
 * @author      Mathieu LESNIAK <mathieu@lesniak.fr>
 * @copyright   Mathieu LESNIAK
 * @package     Suricate
 */
class Validator
{
    private $errors = array();
    private $checks = array();
    private $datas;
    private $value;
    private $index;
    private $stop = false;

    public function __construct($input)
    {
        $this->datas = $input;
        $this->value = $input;
        $this->createChecks();
    }

    private function createChecks()
    {
        $this->checks['equalTo'] = function ($value, $compare) {
            return $value == $compare;
        };

        $this->checks['identicalTo'] = function ($value, $compare) {
            return $value === $compare;
        };

        $this->checks['lessThan'] = function ($value, $compare) {
            return $value < $compare;
        };

        $this->checks['lessThanOrEqual'] = function ($value, $compare) {
            return $value <= $compare;
        };

        $this->checks['greaterThan'] = function ($value, $compare) {
            return $value > $compare;
        };

        $this->checks['greaterThanOrEqual'] = function ($value, $compare) {
            return $value >= $compare;
        };

        $this->checks['blank'] = function ($value) {
            return $value == '';
        };

        $this->checks['null'] = function ($value) {
            return is_null($value);
        };

        $this->checks['true'] = function ($value) {
            return $value === true;
        };

        $this->checks['false'] = function ($value) {
            return !($value === true);
        };

        $this->checks['type'] = function ($value, $type) {
            switch ($type) {
                case 'array':
                    return is_array($value);
                case 'bool':
                    return is_bool($value);
                case 'callable':
                    return is_callable($value);
                case 'float':
                    return is_float($value);
                case 'int':
                    return is_int($value);
                case 'numeric':
                    return is_numeric($value);
                case 'object':
                    return is_object($value);
                case 'resource':
                    return is_resource($value);
                case 'scalar':
                    return is_scalar($value);
                case 'string':
                    return is_string($value);
                default:
                    throw new \InvalidArgumentException('Unknown type to check ' . $type);
            }
        };

        $this->checks['email'] = function ($value) {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        };

        $this->checks['url'] = function ($value) {
            return filter_var($value, FILTER_VALIDATE_URL) !== false;
        };

        $this->checks['ip'] = function ($value) {
            return filter_var($value, FILTER_VALIDATE_IP) !== false;
        };

        $this->checks['regexp'] = function ($value, $regexp) {
            return filter_var($value, FILTER_VALIDATE_REGEXP, $regexp) !== false;
        };

        $this->checks['longerThan'] = function ($value, $length) {
            return strlen($value) > $length;
        };

        $this->checks['longerThanOrEqual'] = function ($value, $length) {
            return strlen($value) >= $length;
        };

        $this->checks['shorterThan'] = function ($value, $length) {
            return strlen($value) < $length;
        };

        $this->checks['shortThanOrEqual'] = function ($value, $length) {
            return strlen($value) <= $length;
        };

        $this->checks['contains'] = function ($value, $toFind) {
            return strpos($value, $toFind) !== false;
        };
        
        $this->checks['alnum'] = function ($value) {
            return ctype_alnum($value);
        };

        $this->checks['alpha'] = function ($value) {
            return ctype_alpha($value);
        };

        $this->checks['digit'] = function ($value) {
            return ctype_digit($value);
        };
        
        $this->checks['lower'] = function ($value) {
            return ctype_lower($value);
        };

        $this->checks['upper'] = function ($value) {
            return ctype_upper($value);
        };

        $this->checks['space'] = function ($value) {
            return ctype_space($value);
        };
    }

    public function validate($index = null)
    {
        if ($index === null) {
            $this->value = $this->datas;
            $this->index = null;
        } else {
            if (is_object($this->datas) && isset($this->datas->$index)) {
                $this->value = $this->datas->$index;
                $this->index = $index;
            } elseif (array_key_exists($index, $this->datas)) {
                $this->value = $this->datas[$index];
                $this->index = $index;
            } else {
                throw new \InvalidArgumentException('Index / Property "' . $index . '" does not exists');
            }
        }

        return $this;
    }

    public function callValidate()
    {
        
        $args = func_get_args();
        if (count($args) < 1) {
            throw new \InvalidArgumentException('bad number of arguments');
        } else {
            $method = array_shift($args);
            // Object method
            if (is_array($method) || is_string($method)) {
                $this->index = null;
                $this->value = call_user_func_array($method, $args);
            } elseif (is_object($method) && ($method instanceof \Closure)) {
                $this->index = null;
                $this->value = call_user_func_array($method, $args);
            } else {
                throw new \InvalidArgumentException('Bad method');
            }
        }

        return $this;
    }

    public function __call($method, $parameters)
    {
        if (!$this->stop) {
            // Stop on error, ignore others tests if fails
            if (substr($method, 0, 4) == 'stop') {
                $stopOnError = true;
                $method = lcFirst(substr($method, 4));
            } else {
                $stopOnError = false;
            }

            // Negation check
            if (substr(strtolower($method), 0, 3) == 'not') {
                $negation   = true;
                $method     = lcFirst(substr($method, 3));
            } else {
                $negation = false;
            }

            if (!isset($this->checks[$method])) {
                throw new \BadMethodCallException('Unknown check ' . $method);
            } else {
                $validator = $this->checks[$method];
            }

            $errorMessage = array_pop($parameters);

            array_unshift($parameters, $this->value);

            $validation = (bool) (call_user_func_array($validator, $parameters) ^ $negation);
            if (!$validation) {
                if ($stopOnError) {
                    $this->stop = true;
                }
                if ($this->index === null) {
                    $this->errors[] = $errorMessage;
                } else {
                    $this->errors[$this->index][] = $errorMessage;
                }
            }
        }

        return $this;
    }

    public function getErrors($index = null)
    {

        if ($index === null) {
            return $this->errors;
        } else {
            return isset($this->errors[$index]) ? $this->errors[$index] : array();
        }
    }

    public function pass()
    {
        return count($this->errors) == 0;
    }

    public function fails()
    {
        return !$this->pass();
    }
}
