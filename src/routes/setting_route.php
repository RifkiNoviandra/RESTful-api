<?php

use Slim\App;
use Slim\Http\Message;
use Slim\Http\Request;
use Slim\Http\Response;

return function(App $app){
    $container = $app->getContainer();

$app->post("/setting/create/setting/" , function(Request $request , Response $response){

    $info = $request->getParsedBody();

    $open_time = isset($info['open_time']) ? $info['open_time'] : '' ;
    $closed_time = isset($info['closed_time']) ? $info['closed_time'] : '' ;
    $status = isset($info['status']) ? $info['status'] : '' ;

    if (empty($info['open_time']) || empty($info['closed_time']) || empty($info['status']) ) {
        
        return $response->withJson(
            [
                "status" => "Field Empty",
                "message" => "Please Fill All Required Field",
                "data" => "0"
            ],
            200
        );

    }else{

        $sql = "INSERT INTO setting(open_time , closed_time , status) VALUES(:open_time , :closed_time , :status)";
        $stmt = $this->db->prepare($sql);

        $data = [

            ":open_time" => $open_time ,
            ":closed_time" => $closed_time,
            ":status" => $status
        ];

        if($stmt->execute($data)){

            return $response->withJson(
                [
                    "status" => "Success",
                    "data" => $data
                ],
                200
            );

        }else{
            return $response->withJson(
                [
                    "status" => "Failed",
                    "data" => "0"
                ],
                200
            );
        }
    }

});

    $app->delete("/setting/delete/setting/{id}" , function(Request $request , Response $response , $args){

        $id = $args['id'];
        $sql = "DELETE FROM setting WHERE setting_id=$id";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {
            return $response->withJson(
                [
                    "status" => "Success",
                    "message" => "Data With ID:$id Has Been Deleted"
                ],
                200
            );
        }else{
            return $response->withJson(
                [
                    "status" => "Failed",
                    "message" => "Data With ID:$id Didn't Exist"
                ],
                200
            );
        }

    });

    $app->put("/setting/update/setting/{id}" , function(Response $response , Request $request , $args){

        $id = $args['id'] ;
        $info = $request->getParsedBody();

        $query = "SELECT FROM setting WHERE setting_id = $id";
        $run = $this->db->prepare($query);
        $run->execute();
        $fetch = $run->fetchAll();


        if (count($fetch) < 1) {
            return $response->withJson(
                [
                    "status" => "Failed",
                    "message" => "This Setting Didn't Exist"
                ],
                200
            );

        }else{

        $open_time = isset($info['open_time']) ? $info['open_time'] : $fetch[0]['open_time'] ;
        $closed_time = isset($info['closed_time']) ? $info['closed_time'] : $fetch[0]['closed_time'] ;
        $status = isset($info['status']) ? $info['status'] : $fetch[0]['status'] ;

        $sql = "UPDATE setting SET open_time = :open_time , closed_time = :closed_time , status = :status";
        $stmt = $this->db->prepare($sql);

        $data = [
            ":open_time" => $open_time,
            ":closed_time" => $closed_time,
            ":status" => $status
        ];

            if($stmt->execute($data)){
                return $response->withJson(
                    [
                        "status" => "success",
                        "message" => "Updated",
                        "data" => $data
                    ],
                    200
                );
            }else{
                return $response->withJson(
                    [
                        "status" => "Failed",
                        "data" => "0"
                    ],
                    200
                );
            }
        }

    });

};