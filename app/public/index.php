<?php

declare(strict_types=1);

// Handling CORS
// Most likely the framework you'll choose to implement this will handle CORS,
// so I'm not too bothered about it here 😎
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Method: GET, POST');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Origin, Accept, X-Requested-With, X-API-KEY");
header('Content-Type: application/json');

require '../vendor/autoload.php';

use App\App;
use App\Services\Ftp\Api\Controller\FtpController;

$app = (new App())
    ->route([
        'name' => 'init_route',
        'path' => '/init',
        'controller' => FtpController::class,
        'method' => 'init'
    ])
    ->route([
        'name' => 'upload_files_route',
        'path' => '/upload-files',
        'controller' => FtpController::class,
        'method' => 'uploadFiles'
    ]);

$app->run();
