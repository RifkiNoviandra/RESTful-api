<?php

use Slim\App;
use Slim\Http\Message;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use Slim\Http\Uri as URI;

return function (App $app) {
    $container = $app->getContainer();

    $app->post("/admin/login", function (Request $request, Response $response) {

        $info = $request->getParsedBody();

        $email = isset($info['email']) ? $info['email'] : '';
        $password = isset($info['password']) ? $info['password'] : '';
        $hash = md5($password);

        $sql = "SELECT * FROM user WHERE email = '$email'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        if (empty($info["email"]) || empty($info["password"])) {
            return $response->withJson(
                [
                    "status" => "field_empty",
                    "message" => "Please Fill All Required Field",
                    "data" => "0"
                ],
                200
            );
        } elseif (count($data) < 1) {
            return $response->withJson(
                [
                    "status" => "Wrong_email",
                    "message" => "Your Email Isn't Registered",
                    "data" => "0"
                ],
                200
            );
        } else {
            if ($data[0]['password'] == $hash) {
                if ($data[0]['role'] == "admin") {
                    return $response->withJson(
                        [
                            "status" => "success",
                            "message" => "logined",
                            "data" => $data,
                        ],
                        200
                    );
                } else {
                    return $response->withJson(
                        [
                            "status" => "Not_Admin",
                            "message" => "You're Not An Admin!!!",

                        ],
                        200
                    );
                }
            } else {
                return $response->withJson(
                    [
                        "status" => "failed",
                        "message" => "You Entered The Wrong Password",

                    ],
                    200
                );
            }
        }
    });

    $app->get("/admin/create/user/", function (Response $response, Request $request) {

        $info = $request->getParsedBody();
        $email = isset($info['email']) ? $info['email'] : '';
        $name = isset($info['name']) ? $info['name'] : '';
        $handphone_number = isset($info['handphone_number']) ? $info['handphone_number'] : '';
        $address = isset($info['address']) ? $info['address'] : '';
        $photo = isset($info['photo']) ? $info['photo'] : '';
        $role = isset($info['role']) ? $info['role'] : '';
        $password = isset($info['password']) ? $info['password'] : '';
        $hash = md5($password);

        $check_email = "SELECT * FROM user WHERE email = '$email'";
        $check_email = $this->db->query($check_email);
        $check_email = $check_email->fetchAll();

        if (empty($info['email']) || empty($info['name']) || empty($info['handphone_number']) || empty($info['password']) || empty($info['address']) || empty($info['photo']) || empty($info['role'])) {

            return $response->withJson(
                [
                    "status" => "failed",
                    "message" => "Please Fill All Required Field",

                ],
                200
            );
        } elseif (count($check_email) > 0) {

            return $response->withJson(
                [
                    "status" => "failed",
                    "message" => "Email Already Taken",

                ],
                200
            );
        } else {

            $sql = "INSERT INTO user (email, name, handphone_number, password , role , address , photo) VALUE (:email, :name, :handphone_number , :password ,:role,:address , :photo)";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":email" => $email,
                ":name" => $name,
                ":handphone_number" => $handphone_number,
                ":password" => $hash,
                ":address" => $address,
                ":photo" => $photo,
                ":role" => $role,
            ];

            if ($stmt->execute($data)) {

                $query_session = "SELECT * FROM user WHERE email = '$email'";
                $session = $this->db->query($query_session);
                $session_data = $session->fetchAll();
                return $response->withJson(["status" => "success", "data" => $session_data], 200);
            } else {
                return $response->withJson(
                    [
                        "status" => "failed",
                        "data" => "0"
                    ],
                    200
                );
            }
        }
    });

    $app->get("/admin/read/user/{id}", function (Request $request, Response $response, $args) {
        $id = isset($args['id']) ? $args['id'] : '';
        $sql = "SELECT * FROM user WHERE user_id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":id" => $id]);
        $result = $stmt->fetchAll();
        if (count($result) > 0)
            return $response->withJson(["status" => "success", "data" => $result], 200);

        else {
            return $response->withJson(
                [
                    "status" => "success",
                    "data" => 0,
                    "message" => "No User with ID $id"
                ],
                200
            );
        }
    });


    $app->delete("/admin/delete/user/{id}", function (Request $request, Response $response, $args) {
        $id = $args["id"];
        $sql = "DELETE FROM user WHERE user_id=:user_id";
        $stmt = $this->db->prepare($sql);

        $data = [
            ":user_id" => $id
        ];

        if ($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);

        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });



    $app->get("/admin/register/school", function (Request $request, Response $response) {
        $request->getParsedBody();
        $nspn = isset($info['nspn']) ? $info['nspn'] : '';
        $name = isset($info['name']) ? $info['name'] : '';
        $photo = isset($info['photo']) ? $info['photo'] : '';
        $address = isset($info['address']) ? $info['address'] : '';
        $principal = isset($info['principal']) ? $info['principal'] : '';
        $fax = isset($info['fax']) ? $info['fax'] : '';

        if (empty($info['nspn']) || empty($info['name']) || empty($info['photo']) || empty($info['address']) || empty($info['principal']) || empty($info['fax'])) {
            return $response->withJson(
                [
                    "status" => "field_empty",
                    "message" => "Please Fill All Required Field",
                    "data" => "0"
                ],
                200
            );
        } else {
            $sql = "SELECT * FROM school WHERE nspn=:nspn";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();

            if (count($data > 0)) {
                return $response->withJson(
                    [
                        "status" => "failed",
                        "message" => "School With NSPN : $nspn Is Already Added",
                        "data" => "0"
                    ],
                    200
                );
            } else {
                $query = "INSERT INTO school(nspn,name,photo,address,principal,fax) VALUES(:nspn,:name,:photo,:address,:principal,:fax)";
                $insert = $this->db->prepare($query);

                $inserted_data = [
                    ":nspn" => $nspn,
                    ":name" => $name,
                    ":photo" => $photo,
                    ":address" => $address,
                    ":principal" => $principal,
                    ":fax" => $fax
                ];

                if ($insert->execute($data)) {
                    return $response->withJson(
                        [
                            "status" => "success",
                            "data" => $inserted_data
                        ],
                        200
                    );
                } else {
                    return $response->withJson(
                        [
                            "status" => "failed",
                            "data" => "0"
                        ],
                        200
                    );
                }
            }
        }
    });

    // endadmin
};
