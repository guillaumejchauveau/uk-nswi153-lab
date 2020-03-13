<?php

/*
 * Initial parameters, which are loaded from command line.
 * Each static member variable is annotated by doc-comments, so it can be automatically parsed
 * and a help (usage) can be automatically printed.
 * Possible annotations:
 * @required - if present, the argument is mandatory
 * @string, @int, @bool - define a data type of the argument value (bool has no value, argument presence ~ true, false should be default).
 * @short(str) - shorthand argument string (e.g., $base has @short(b), which means the argument can be written as --base or -b).
 * 		Note that aggregation (-b -l written as -bl) of short arguments is not required
 */

class Args
{
    /**
     * The URL base for generating relative URLs.
     * The base must match complete prefix of an URL to be applied.
     * @required @string @short(b)
     */
    public static $base = null;

    /**
     * If present, <a> elements are left unchanged.
     * @bool
     */
    public static $no_links = false;

    /**
     * If present, <img> elements are left unchanged.
     * @bool
     */
    public static $no_imgs = false;

    /**
     * If present, all other elements except for <a> and <img> (i.e., <script>, <form>, <frame>, ...) are left unchanged.
     * @bool
     */
    public static $no_others = false;


    /**
     * Minimal length, that an absolute URL must have to be replaced. Shorter URLs will remain absolute.
     * @int @short(l)
     */
    public static $length = null;


    public static function printUsage(string $scriptName, array $argsDefinitions)
    {
        echo "Usage: $scriptName ";
        foreach ($argsDefinitions as $argsDefinition) {
            if ($argsDefinition['required']) {
                echo '--' . $argsDefinition['name'] . ' ';
                if ($argsDefinition['type'] !== 'bool') {
                    echo '<' . $argsDefinition['type'] . '> ';
                }
            }
        }
        echo "[options] [args]\n\n";
        echo "Options:\n\n";
        foreach ($argsDefinitions as $argsDefinition) {
            echo '    ';
            if ($argsDefinition['short']) {
                echo '-' . $argsDefinition['short'] . ' | ';
            }
            echo '--' . $argsDefinition['name'];
            if ($argsDefinition['type'] !== 'bool') {
                echo ' <' . $argsDefinition['type'] . '>';
            }
            echo "\n";
        }
    }

    /*
     * Parsing Methods
     */


    /**
     * Load the arguments from an array (e.g., the $argv may be passed down right away).
     * First value of the args array is expected to be path to this script.
     * @param array $args
     * @return array Remaining unprocessed arguments from the $args array.
     * @throws ReflectionException
     * @throws Exception
     */
    public static function load(array $args)
    {
        $script_name = array_shift($args); // remove the path

        $argDefinitions = [];
        $known_shorts = [];

        $reflection = new ReflectionClass(self::class);
        $remainingRequiredArgs = [];
        foreach ($reflection->getStaticProperties() as $property => $value) {
            $propertyReflection = new ReflectionProperty(self::class, $property);
            $doc = $propertyReflection->getDocComment();
            $required = strpos($doc, '@required') !== false;
            preg_match('/@(string|int|bool)/', $doc, $type);
            preg_match('/@short\((\w)\)/', $doc, $short);
            $argDefinition = [
                'name' => $property,
                'required' => $required,
                'type' => $type[1] ?? 'bool',
                'short' => $short[1] ?? null
            ];
            $argDefinitions[] = $argDefinition;
            if ($required) {
                $remainingRequiredArgs[$property] = $argDefinition;
            }
            if ($argDefinition['short']) {
                $short = $argDefinition['short'];
                if (in_array($short, $known_shorts)) {
                    throw new Exception("Duplicated short '$short'");
                }
                $known_shorts[] = $short;
            }
        }
        unset($argDefinition);
        if (empty($argDefinitions)) {
            return $args;
        }

        while (!empty($args)) {
            if ($args[0][0] !== '-') {
                break;
            }
            $current = array_shift($args);
            $argDefinition = null;
            if (strlen($current) === 2) {
                $short = $current[1];
                if ($short === 'h') {
                    self::printUsage($script_name, $argDefinitions);
                    exit(1);
                }
                foreach ($argDefinitions as $argDefinition) {
                    if ($argDefinition['short'] === $short) {
                        break;
                    }
                    $argDefinition = null;
                }
            } elseif (strlen($current) > 2 && $current[1] === '-') {
                $name = substr($current, 2);
                if ($name === 'help') {
                    self::printUsage($script_name, $argDefinitions);
                    exit(1);
                }
                foreach ($argDefinitions as $argDefinition) {
                    if ($argDefinition['name'] === $name) {
                        break;
                    }
                    $argDefinition = null;
                }
            }

            if ($argDefinition === null) {
                echo "Invalid argument '" . $current . "'\n";
                self::printUsage($script_name, $argDefinitions);
                exit(1);
            }

            $name = $argDefinition['name'];

            if ($argDefinition['required']) {
                unset($remainingRequiredArgs[$name]);
            }

            if ($argDefinition['type'] !== 'bool') {
                if (empty($args) || $args[0][0] === '-') {
                    echo "Argument '" . $name . "' expects a value\n";
                    self::printUsage($script_name, $argDefinitions);
                    exit(1);
                }
                $value = array_shift($args);
                if ($argDefinition['type'] === 'int') {
                    if (!is_numeric($value)) {
                        echo "Argument '" . $name . "' expects an integer as value\n";
                        self::printUsage($script_name, $argDefinitions);
                        exit(1);
                    }
                    $value = intval($value);
                }
                static::$$name = $value;
            } else {
                static::$$name = true;
            }
        }

        if (count($remainingRequiredArgs) > 0) {
            echo 'Missing required arguments: ';
            foreach ($remainingRequiredArgs as $remainingRequiredArg) {
                echo "'" . $remainingRequiredArg['name'] . "' ";
            }
            echo "\n";
            self::printUsage($script_name, $argDefinitions);
            exit(1);
        }

        return $args;
    }
}
