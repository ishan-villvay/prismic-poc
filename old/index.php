
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require 'vendor/autoload.php'; // Load Composer's autoloader


AppFactory::setSlimHttpDecoratorsAutomaticDetection(false);
ServerRequestCreatorFactory::setSlimHttpDecoratorsAutomaticDetection(false);

// Instantiate App
$app = AppFactory::create();

// Add error middleware
// $app->addErrorMiddleware(true, true, true);

// Add routes
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('<a href="/hello/world">Try /hello/world</a>');
    return $response;
});

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$app->run();


// require 'vendor/autoload.php'; // Load Composer's autoloader

// use Nyholm\Psr7\Factory\Psr17Factory;
// use Slim\Factory\AppFactory;

// // Create PSR-17 factories
// $psr17Factory = new Psr17Factory();

// // Create Slim app with custom AppFactory
// $app = AppFactory::createFromPsr17($psr17Factory, $psr17Factory, $psr17Factory);

// // Handle GET request for /categories endpoint
// $app->get('/categories', function ($request, $response, $args) {
//     // Get Categories instance through dependency injection
//     $categories = $this->get('Categories');

//     // Return JSON response with categories
//     $response->getBody()->write(json_encode($categories->getCategories()));
//     return $response->withHeader('Content-Type', 'application/json');
// });

// // Create container
// $container = $app->getContainer();

// // Register Categories class in the container
// $container->set('Categories', function ($container) {
//     return new Categories();
// });

// // Run App
// $app->run(); -->
