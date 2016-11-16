<?php

require_once __DIR__.'/vendor/autoload.php';

use Monga\Query\ParseQuery;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

if (!file_exists(__DIR__.'/app/config/parameters.yml')) {
    die('parameters.yml not found');
}

try {
    $params = Yaml::parse(file_get_contents(__DIR__.'/app/config/parameters.yml'));
} catch (ParseException $e) {
    printf('Unable to parse the YAML string: %s', $e->getMessage());
}

// Get a connection
$connection = \League\Monga::connection($params['parameters']['database_server']);

// Get the database
$database = $connection->database('db_name');

// Drop the database
$database->drop();

// Get a collection
/** @var \League\Monga\Collection $collection */
$collection = $database->collection('collection_name');

// Drop the collection
$collection->drop();

// Truncate the collection
$collection->truncate();

// Insert some values into the collection
$insertIds = $collection->insert([
    [
        'name' => 'John',
        'surname' => 'Doe',
        'nick' => 'The Unknown Man',
        'age' => 20,
    ],
    [
        'name' => 'Frank',
        'surname' => 'de Jonge',
        'nick' => 'Unknown',
        'age' => 23,
    ],
    [
        'name' => 'Kurt',
        'surname' => 'Permi',
        'nick' => 'kert',
        'age' => 23,
    ],
    [
        'name' => 'Kurt',
        'surname' => 'Permi',
        'nick' => 'kert',
        'age' => 23,
    ],
]);

echo "
Write query\n
Like:
SELECT [<Projections>] [FROM <Target>]
	[WHERE <Condition>*]
	[GROUP BY <Field>*]
	[ORDER BY <Fields>* [ASC|DESC] *]
	[SKIP <SkipRecords>]
	[LIMIT <MaxRecords>]
";
echo ParseQuery::PROJECTION. " ";
$stdin = fopen('php://stdin', 'p');
$projections = fgets($stdin, 80);
$arrayQuery = [];
if ($projections !== null) {
    $arrayQuery[ParseQuery::PROJECTION] = $projections;
    echo ParseQuery::TARGET. " ";    
    $stdin = fopen('php://stdin', 'a');
    $target = fgets($stdin, 80);
    if ($target !== null) {
        $arrayQuery[ParseQuery::TARGET] = $projections;    
        echo ParseQuery::CONDITION." ";
        $stdin = fopen('php://stdin', 'b');
        $condition = fgets($stdin, 80);
        if ($condition !== null) {
            $arrayQuery[ParseQuery::CONDITION] = $condition;
            echo ParseQuery::ORDER_BY_FIELD . " ";
            $stdin = fopen('php://stdin', 'b');
            $groupByField = fgets($stdin, 80);
            if ($groupByField !== null) {
                $arrayQuery[ParseQuery::ORDER_BY_FIELD] = $groupByField;
                echo ParseQuery::SKIP_RECORDS." ";
                $stdin = fopen('php://stdin', 'b');
                $skipRecords = fgets($stdin, 80);
                if ($skipRecords !== null) {
                    $arrayQuery[ParseQuery::SKIP_RECORDS] = $skipRecords;
                    echo ParseQuery::MAX_RECORDS." ";
                    $stdin = fopen('php://stdin', 'b');
                    $maxRecords = fgets($stdin, 80);
                    if ($maxRecords !== null) {
                        $arrayQuery[ParseQuery::MAX_RECORDS] = $maxRecords;    
                    }
                }
            }
        }
    }
}

$result = new \Monga\Query\ParseQuery($arrayQuery);
$returnConsole = '';
$return = $result->parseQuery($collection);
foreach ($return as $key => $value) {
    $id = (array) $value['_id'];
    $return[$key]['_id'] = $id['$id'];
    if (isset($return[$key]['createdAt'])) {
        $mongoDate = $return[$key]['createdAt'];
        /* @var \MongoDate $mongoDate */
        $return[$key]['createdAt'] = $mongoDate->toDateTime()->format('Y-m-d');
    }

    if (isset($return[$key]['updatedAt'])) {
        $mongoDate = $return[$key]['updatedAt'];
        /* @var \MongoDate $mongoDate */
        $return[$key]['updatedAt'] = $mongoDate->toDateTime()->format('Y-m-d');
    }
    $output = implode(', ', array_map(
        function ($v, $k) { return sprintf("%s='%s'", $k, $v); },
        $return[$key],
        array_keys($return[$key])
    ));
    $returnConsole .= $output."\n";
}
echo $returnConsole;
