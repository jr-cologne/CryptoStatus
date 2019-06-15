<?php

switch (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) {
    case '/cryptostatus/hourly':
        require_once '../app/app.php';
        break;
    default:
        http_response_code(404);
        exit('Not Found: Invalid or unknown path requested.');
}
