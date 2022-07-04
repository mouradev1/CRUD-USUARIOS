<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require '../config/database.php';
$app = AppFactory::create();

$app->get('/usuarios', function (Request $request, Response $response, $args): Response {
    try {
        $db = new Db();
        $conn = $db->connect();
        $sql = 'SELECT name,usuario FROM usuarios';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (\Throwable $th) {
        $response->getBody()->write($th->getMessage());
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


$app->post('/usuarios', function (Request $request, Response $response, $args): Response {
    try {
        $data = $request->getParsedBody();
        $db = new Db();
        $conn = $db->connect();
        $stmt = $conn->prepare('INSERT INTO usuarios (name,usuario,password) VALUES (:name,:usuario, :password)');
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':usuario', $data['usuario']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->execute();
        $conn = null;
        $response->getBody()->write(json_encode([
            'msg' => 'Usuario creado com sucesso!!'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch (Exception $e) {
        $response->getBody()->write($e->getMessage());
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->get('/user/{id}', function (Request $request, Response $response, $args): Response {
    try {
        $id = $args['id'];
        $db = new Db();
        $conn = $db->connect();
        $sql = 'SELECT name,usuario FROM usuarios WHERE id = :id';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $response->getBody()->write(json_encode($user));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode([
                'msg' => 'Usuario não encontrado!!'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    } catch (\Throwable $th) {
        $response->getBody()->write($th->getMessage());
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});
