<?php

use Slim\App;
use Slim\Http\Message;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use Slim\Http\Uri as URI;

return function (App $app) {
    $container = $app->getContainer();

    $app->get("/user/search/{param}", function (Request $request, Response $response, $args) {
        $keyword = isset($args['param']) ? $args['param'] : '';
        $sql = "SELECT * FROM user WHERE name LIKE '%$keyword%' OR handphone_number LIKE '%$keyword%' OR email LIKE '%$keyword%'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });
};
