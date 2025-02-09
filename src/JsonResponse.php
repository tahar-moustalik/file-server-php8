<?php

namespace App;

class JsonResponse {


    public function sendResponse($data,$status) {

        \http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);exit;

    }

}