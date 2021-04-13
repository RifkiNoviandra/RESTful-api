<?php

use Slim\App;
use Slim\Http\Message;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

    // $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
    //     // Sample log message
    //     $container->get('logger')->info("Slim-Skeleton '/' route");

    //     // Render index view
    //     return $container->get('renderer')->render($response, 'index.phtml', $args);
    // });

    $app->get("/user/login" , function(Request $request , Response $response){

        $info = $request->getParsedBody();

        $email = isset ($info['email']) ? $info['email']:'';
        $password = isset ($info['password']) ? $info['password']:'';

        $sql = "SELECT * FROM user WHERE email = '$email'";
        $stmt = $this->db->prepare($sql);
        $stmt ->execute();
        $data = $stmt->fetchAll();

        if(empty($info["email"]) || empty($info["password"])){
            return $response->withJson(
                [
                    "status" => "failed",
                    "message" => "Please Fill All Required Field",
                    "data" => "0"
                ],200
            );
        }elseif(count($data) < 1){
            return $response->withJson(
                [
                    "status" => "failed",
                    "message" => "Your Email Isn't Registered",
                    "data" => "0"
                ],200
            );
        }else{
            if( $data[0]['password'] == $password ){
                return $response->withJson(
                    [
                        "status" => "success",
                        "message" => "logined",
                        "data" => $data,

                    ],200
                );
            }else{
                return $response->withJson(
                    [
                        "status" => "failed",
                        "message" => "You Entered The Wrong Password",

                    ],200
                );
            }
        }

    });


    $app->get("/user", function (Request $request, Response $response){
        $sql = "SELECT * FROM user";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });
    
    $app->get("/user/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $sql = "SELECT * FROM user WHERE user_id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":id" => $id]);
        $result = $stmt->fetchAll();
        if(count($result) > 0)
        return $response->withJson(["status" => "success", "data" => $result], 200);

        else{
            return $response->withJson(
                [
                    "status" => "success",
                    "data" => 0,
                    "message" => "No User with ID $id"
                ], 200
            );
        }
    });
    
    $app->get("/user/search/{param}", function (Request $request, Response $response, $args){
        $keyword = $args["param"];
        $sql = "SELECT * FROM user WHERE name LIKE '%$keyword%' OR handphone_number LIKE '%$keyword%' OR email LIKE '%$keyword%'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });
    
    $app->post("/user/register", function (Request $request, Response $response){
    
        $info = $request->getParsedBody();
        
        $email = isset ($info['email']) ? $info['email']:'';
        $name = isset ($info['name']) ? $info['name']:'';
        $handphone_number = isset ($info['handphone_number']) ? $info['handphone_number']:'';
        $password = isset ($info['password']) ? $info['password']:'';

        $sql = "INSERT INTO user (email, name, handphone_number, password , role) VALUE (:email, :name, :handphone_number , :password ,:role)";
        $stmt = $this->db->prepare($sql);
        $check_email = "SELECT * FROM user WHERE email = '$email'";
        $check_email = $this->db->query($check_email);
        $check_email = $check_email->fetchAll();
        if(empty($info["email"]) || empty($info["name"]) || empty($info["handphone_number"]) || empty($info["password"]) ){
            return $response->withJson(
                [
                "status" => "failed",
                "message" => "Please Fill All Required Field",
                "data" => "0"
                ], 200
            );
        }
        elseif( count($check_email) > 0){

            return $response->withJson(
                [
                "status" => "failed",
                "message" => "Email Already Taken",
                "data" => "0"
                ], 200
            );
        }
        else{
            $data = [
                ":email" => $email,
                ":name" => $name,
                ":handphone_number" => $handphone_number,
                ":password" => $password,
                ":role" => "user",
        ];
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(
                [
                    "status" => "failed", 
                    "data" => "0"
                ],200
            );
        }
    });

    $app->get("/admin/login" , function(Request $request , Response $response){

        $info = $request->getParsedBody();

        $email = isset ($info['email']) ? $info['email']:'';
        $password = isset ($info['password']) ? $info['password']:'';

        $sql = "SELECT * FROM user WHERE email = '$email'";
        $stmt = $this->db->prepare($sql);
        $stmt ->execute();
        $data = $stmt->fetchAll();

        if(empty($info["email"]) || empty($info["password"])){
            return $response->withJson(
                [
                    "status" => "field_empty",
                    "message" => "Please Fill All Required Field",
                    "data" => "0"
                ],200
            );
        }elseif(count($data) < 1){
            return $response->withJson(
                [
                    "status" => "Wrong_email",
                    "message" => "Your Email Isn't Registered",
                    "data" => "0"
                ],200
            );
        }else{
            if( $data[0]['password'] == $password ){
                if($data[0]['role'] == "admin"){
                    return $response->withJson(
                        [
                            "status" => "success",
                            "message" => "logined",
                            "data" => $data,
                        ],200
                    );
                }else{
                    return $response->withJson(
                        [
                            "status" => "Not_Admin",
                            "message" => "You're Not An Admin!!!",
    
                        ],200
                    );
                }
            }else{
                return $response->withJson(
                    [
                        "status" => "failed",
                        "message" => "You Entered The Wrong Password",

                    ],200
                );
            }
        }

    });
    
    
//     $app->put("/siswa/{nis}", function (Request $request, Response $response, $args){
//         $nis = $args["nis"];
//         $info = $request->getParsedBody();
//         $sql = "UPDATE biodata SET nama=:nama, kelas=:kelas, absen=:absen , nomor=:absen WHERE nis=:nis";
//         $stmt = $this->db->prepare($sql);
//         $data = [
//             ":nis" => $nis,
//             ":nama" => $info["nama"],
//             ":kelas" => $info["kelas"],
//             ":absen" => $info["absen"],
//             ":nomor" => $info["nomor"]
//         ];
    
//         if($stmt->execute($data))
//            return $response->withJson(["status" => "success", "data" => "1"], 200);
        
//         return $response->withJson(["status" => "failed", "data" => "0"], 200);
//     });
    
    
//     $app->delete("/siswa/{nis}", function (Request $request, Response $response, $args){
//         $nis = $args["nis"];
//         $sql = "DELETE FROM biodata WHERE nis=:nis";
//         $stmt = $this->db->prepare($sql);
        
//         $data = [
//             ":nis" => $nis
//         ];
    
//         if($stmt->execute($data))
//            return $response->withJson(["status" => "success", "data" => "1"], 200);
        
//         return $response->withJson(["status" => "failed", "data" => "0"], 200);
//     });
};
