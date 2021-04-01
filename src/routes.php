<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });

    $app->get("/siswa/", function (Request $request, Response $response){
        $sql = "SELECT * FROM biodata";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });
    
    $app->get("/siswa/{nis}", function (Request $request, Response $response, $args){
        $nis = $args["nis"];
        $sql = "SELECT * FROM books WHERE id_biodata=:nis";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":nis" => $nis]);
        $result = $stmt->fetch();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });
    
    $app->get("/siswa/search/{param}", function (Request $request, Response $response, $args){
        $keyword = $args["param"];
        $sql = "SELECT * FROM biodata WHERE nis LIKE '%$keyword%' OR nama LIKE '%$keyword%' OR kelas LIKE '%$keyword%'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });
    
    $app->post("/siswa/", function (Request $request, Response $response){
    
        $info = $request->getParsedBody();
    
        $sql = "INSERT INTO biodata (nis, nama, kelas,absen,nomor) VALUE (:nis, :nama, :kelas , :absen, :nomor)";
        $stmt = $this->db->prepare($sql);
    
        $data = [
            ":nis" => $info["nis"],
            ":nama" => $info["nama"],
            ":kelas" => $info["kelas"],
            ":absen" => $info["absen"],
            ":nomor" => $info["nomor"]
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });
    
    
    $app->put("/siswa/{nis}", function (Request $request, Response $response, $args){
        $nis = $args["nis"];
        $info = $request->getParsedBody();
        $sql = "UPDATE biodata SET nama=:nama, kelas=:kelas, absen=:absen , nomor=:absen WHERE nis=:nis";
        $stmt = $this->db->prepare($sql);
        $data = [
            ":nis" => $nis,
            ":nama" => $info["nama"],
            ":kelas" => $info["kelas"],
            ":absen" => $info["absen"],
            ":nomor" => $info["nomor"]
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });
    
    
    $app->delete("/siswa/{nis}", function (Request $request, Response $response, $args){
        $nis = $args["nis"];
        $sql = "DELETE FROM biodata WHERE nis=:nis";
        $stmt = $this->db->prepare($sql);
        
        $data = [
            ":nis" => $nis
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });
};
