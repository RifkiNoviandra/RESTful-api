<?php

use Slim\App;
use Slim\Http\Message;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use Slim\Http\Uri as URI;

return function (App $app) {
    $container = $app->getContainer();

    $app->post("/user/login", function (Request $request, Response $response) {

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
                    "status" => "failed",
                    "message" => "Please Fill All Required Field",
                    "data" => "0"
                ],
                200
            );
        } elseif (count($data) < 1) {
            return $response->withJson(
                [
                    "status" => "failed",
                    "message" => "Your Email Isn't Registered",
                    "data" => "0"
                ],
                200
            );
        } else {
            if ($data[0]['password'] == $hash) {
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
                        "status" => "failed",
                        "message" => "You Entered The Wrong Password",

                    ],
                    200
                );
            }
        }
    });

    $app->post("/user/register", function (Request $request, Response $response) {

        $info = $request->getParsedBody();

        $email = isset($info['email']) ? $info['email'] : '';
        $name = isset($info['name']) ? $info['name'] : '';
        $handphone_number = isset($info['handphone_number']) ? $info['handphone_number'] : '';
        $password = isset($info['password']) ? $info['password'] : '';
        $hash = md5($password);

        $sql = "INSERT INTO user (email, name, handphone_number, password , role) VALUE (:email, :name, :handphone_number , :password ,:role)";
        $stmt = $this->db->prepare($sql);
        $check_email = "SELECT * FROM user WHERE email = '$email'";
        $check_email = $this->db->query($check_email);
        $check_email = $check_email->fetchAll();
        if (empty($info["email"]) || empty($info["name"]) || empty($info["handphone_number"]) || empty($info["password"])) {
            return $response->withJson(
                [
                    "status" => "failed",
                    "message" => "Please Fill All Required Field",
                    "data" => "0"
                ],
                200
            );
        } elseif (count($check_email) > 0) {

            return $response->withJson(
                [
                    "status" => "failed",
                    "message" => "Email Already Taken",
                    "data" => "0"
                ],
                200
            );
        } else {
            $data = [
                ":email" => $email,
                ":name" => $name,
                ":handphone_number" => $handphone_number,
                ":password" => $hash,
                ":role" => "user",
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

    $app->get("/user/read/{id}", function (Request $request, Response $response, $args) {
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

    $app->put("/user/update/{id}", function (Request $request, Response $response, $args) {
        $id = $args["id"];
        $info = $request->getParsedBody();
        $image = $request->getUploadedFiles();
        $sql = "SELECT * FROM user WHERE user_id=$id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $fetch = $stmt->fetchAll();
        if (count($fetch) < 1) {
            return $response->withJson(
                [
                    "status" => "success",
                    "message" => "No Data With User_id $id",
                    "data" => 0
                ],
                200
            );
        } else {
            if ($image['photo']->getError() === UPLOAD_ERR_OK) {

                if (isset($image['photo'])) {

                    $user_image = $image['photo'];
                    $extension = pathinfo($user_image->getClientFilename(), PATHINFO_EXTENSION);
                    $photo = sprintf('%s.%0.8s', $args["id"], $extension);
                    $directory = $this->get('settings')['upload_directory'];
                    $user_image->moveTo($directory . DIRECTORY_SEPARATOR . $photo);
                } else {

                    $photo = $fetch[0]['photo'];
                }

                $email = isset($info['email']) ? $info['email'] : $fetch[0]['email'];
                $name = isset($info['name']) ? $info['name'] : $fetch[0]['name'];
                $handphone_number = isset($info['handphone_number']) ? $info['handphone_number'] : $fetch[0]['handphone_number'];
                $address = isset($info['address']) ? $info['address'] : $fetch[0]['address'];

                $query = "UPDATE user SET email=:email , name=:name , photo=:photo , handphone_number = :handphone_number , address=:address  WHERE user_id=$id";
                $update = $this->db->prepare($query);
                $data = [
                    ":email" => $email,
                    ":name" => $name,
                    ":photo" => $photo,
                    ":handphone_number" => $handphone_number,
                    ":address" => $address,
                ];
                if ($update->execute($data)) {
                    $url = __DIR__ . "public/user_image/" . $photo;
                    return $response->withJson(["status" => "success", "data" => $data, "url" => $url], 200);
                } else {
                    return $response->withJson(["status" => "failed", "data" => "0"], 200);
                }
            }
        }
    });

    $app->post("/user/attendance/", function (Request $request, Response $response) {
        $info = $request->getParsedBody();
        $user_id = isset($info['user_id']) ? $info['user_id'] : '';
        $date = isset($info['date']) ? $info['date'] : '';
        $time = isset($info['time']) ? $info['time'] : '';
        $location = isset($info['location']) ? $info['location'] : '';

        $sql = "SELECT * FROM setting";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $input_time = strtotime($time);
        $set_time = strtotime($data[0]['closed_time']);
        $open_time = strtotime($data[0]['open_time']);
        // $diff = $set_time - $input_time ;
        // $diff = round(abs($diff)/60,2);

        if (empty($info['user_id']) || empty($info['date']) || empty($info['time']) || empty($info['location'])) {
            return $response->withJson(
                [
                    "status" => "failed",
                    "massage" => "Please Fill All Required Field",
                    "data" => "0"
                ],
                200
            );
        }

        if ($input_time >= $open_time) {

            if ($input_time <= $set_time) {
                $status = "hadir";
            } elseif ($input_time > $set_time) {
                $status = "terlambat";
            }

            $insert = "INSERT INTO attendance (user_id , date , time , location , status) VALUES (:user_id , :date , :time , :location , :status)";
            $query = $this->db->prepare($insert);
            $data = [
                ":user_id" => $user_id,
                ":date" => $date,
                ":time" => $time,
                ":location" => $location,
                ":status" => $status
            ];

            if ($query->execute($data))
                return $response->withJson(
                    [
                        "status" => "success",
                        "message" => "Your Attendance Has Been Recorded",
                        "data" => $data
                    ],
                    200
                );

            return $response->withJson(
                [
                    "status" => "failed",
                    "data" => "0"
                ],
                200
            );
        } else {
            return $response->withJson(
                [
                    "status" => "failed",
                    "Message" => "attendance has not opened yet",
                    "data" => "0"
                ],
                200
            );
        }
    });

    $app->post("/user/permittance/", function (Request $request, Response $response) {
        $info = $request->getParsedBody();
        $user_id = isset($info['user_id']) ? $info['user_id'] : '';
        $date = isset($info['date']) ? $info['date'] : '';
        $time = isset($info['time']) ? $info['time'] : '';
        $reason = isset($info['reason']) ? $info['reason'] : '';
        $permit_time = isset($info['permittance_time']) ? $info['permittance_time'] : '';
        $proof = isset($info['proof']) ? $info['proof'] : '';

        $sql = "SELECT * FROM user WHERE user_id = '$user_id'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $fetch = $stmt->fetch();

        if (empty($info['user_id']) || empty($info['date']) || empty($info['time']) || empty($info['reason']) || empty($info['permittance_time']) || empty($info['proof'])) {
            return $response->withJson(
                [
                    "status" => "failed",
                    "Message" => "Please Fill All Required Fields",
                    "data" => "0"
                ],
                200
            );
        } else {
            $query = "INSERT INTO permittance(user_id,name,date,time,reason,permittance_time,proof) VALUES(:user_id , :name,:date , :time , :reason ,:permit_time,:proof)";
            $input = $this->db->prepare($query);

            $data = [
                ":user_id" => $user_id,
                ":name" => $fetch['name'],
                ":time" => $time,
                ":date" => $date,
                ":reason" => $reason,
                ":permit_time" => $permit_time,
                ":proof" => $proof
            ];

            if ($input->execute($data)) {
                return $response->withJson(
                    [
                        "status" => "succes",
                        "data" => $data
                    ],
                    200
                );
            } else {
                return $response->withJson(
                    [
                        "status" => "failed",
                        "Message" => "Please Fill All Required Fields",
                        "data" => "0"
                    ],
                    200
                );
            }
        }
    });

    $app->post("/user/report/", function (Request $request, Response $response) {
        $info = $request->getParsedBody();
        $user_id = isset($info['user_id']) ? $info['user_id'] : '';
        $date = isset($info['date']) ? $info['date'] : '';
        $new_knowledge = isset($info['new_knowledge']) ? $info['new_knowledge'] : '';
        $photo = isset($info['photo']) ? $info['photo'] : '';

        $sql = "SELECT * FROM user WHERE user_id = '$user_id'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch();

        if (empty($info['user_id']) || empty($info['date']) || empty($info['new_knowledge']) || empty($info['photo'])) {
            return $response->withJson(
                [
                    "status" => "failed",
                    "Message" => "Please Fill All Required Fields",
                    "data" => "0"
                ],
                200
            );
        } else {
            $query = "INSERT INTO report(user_id,name,date,new_knowledge,photo) VALUES(:user_id , :name,:date ,:new_knowledge , :photo)";
            $input = $this->db->prepare($query);

            $data = [
                ":user_id" => $user_id,
                ":name" => $data['name'],
                ":date" => $date,
                ":new_knowledge" => $new_knowledge,
                ":photo" => $photo
            ];

            if ($input->execute($data)) {
                return $response->withJson(
                    [
                        "status" => "succes",
                        "data" => $data
                    ],
                    200
                );
            } else {
                return $response->withJson(
                    [
                        "status" => "failed",
                        "Message" => "Please Fill All Required Fields",
                        "data" => "0"
                    ],
                    200
                );
            }
        }
    });
};
