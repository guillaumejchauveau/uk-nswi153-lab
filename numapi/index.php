<?php
/**
 * Num
 * @version 1.0.0
 */

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Slim\App();


/**
 * GET negation
 * Summary: Negates a number
 * Notes:
 * Output-Formats: [application/json]
 */
$app->get('/unary/negation', function (Request $request, Response $response, $args) {
    $queryParams = $request->getQueryParams();
    $input = $queryParams['input'];
    $result = -$input;
    return $response->withJson(['result' => $result]);
});

/**
 * GET binaryOp
 * Summary: An operation on two numbers
 * Notes:
 * Output-Formats: [application/json]
 */
$app->get('/binary/{op}', function (Request $request, Response $response, $args) {
    $queryParams = $request->getQueryParams();
    $left = $queryParams['left'];
    $right = $queryParams['right'];

    if ($args['op'] === 'add') {
        $result = $left + $right;
    } else if ($args['op'] === 'sub') {
        $result = $left - $right;
    } else {
        return $response->withStatus(StatusCode::HTTP_BAD_REQUEST);
    }
    return $response->withJson(['result' => $result]);
});

/**
 * POST sum
 * Summary: Sums all numbers
 * Notes:
 * Output-Formats: [application/json]
 */
$app->post('/sum', function (Request $request, Response $response, $args) {
    $inputs = $request->getParsedBody();
    $result = array_sum($inputs);
    return $response->withJson(['result' => $result]);
});


$app->run();
