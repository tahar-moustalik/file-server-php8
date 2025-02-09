<?php

error_reporting(E_ALL);

ini_set('display_errors', 1);


require_once __DIR__ . '/../autoload.php';



$handler = new App\LocalFileHandler();


$middleware = new App\SecurityMiddleware();
$jsonResponse = new App\JsonResponse();


if(!$middleware->check()) {


    $jsonResponse->sendResponse(['message' => 'Unauthorised'],405);
    
}





// routing 

$uri = $_SERVER['REQUEST_URI'] ?? null;
if($uri) {

    switch($uri) {

        case "/upload": 

            $files = $_FILES ?? [];
            $destination = $_POST['destination'] ?? '';

            $result = $handler->upload($files,$destination);

            $jsonResponse->sendResponse($result,$result['statusCode']);
            break;
        
        case "/download": 
             $filePath = $_POST['filePath'] ?? '';

             $handler->download($filePath);
            
             break;
        case "/delete":
                  
            $filePath = $_POST['filePath'] ?? '';

            $result = $handler->delete($filePath);

            $jsonResponse->sendResponse($result,$result['statusCode']);

            break;
        case "/move":
                      
                $filePath = $_POST['filePath'] ?? '';
                $destination = $_POST['destination'] ?? '';
    
                $result = $handler->move($filePath,$destination);
    
                $jsonResponse->sendResponse($result,$result['statusCode']);
                
                break;
        case "/metadata":
                      
                $filePath = $_POST['filePath'] ?? '';
        
                $result = $handler->getMetadata($filePath);
        
                $jsonResponse->sendResponse($result,$result['statusCode']);
                    
                break;     
        case "/downloadZip":
                      
                    $directoryPath = $_POST['directoryPath'] ?? '';
            
                   $handler->downloadDirectory($directoryPath);
            
                        
                    break;             
        default:
            http_response_code(404);
            exit('404 Not Found');
           break;    
    }

} else {

    http_response_code(404);
    exit('404 Not Found');
}