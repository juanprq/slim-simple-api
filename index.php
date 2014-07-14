<?php 
// Carga de librerías de composer
require 'vendor/autoload.php';

// Se cargan los parámetros iniciales de configuración para slim.
$config = require_once __DIR__ . '/config.php';

// Se inicializa la aplicación slim con las propiedades cargadas del archivo para slim
$app = new \Slim\Slim($config['slim']);

// Se configura la aplicación para que responda en formato JSON.
$app->response->headers->set('Content-Type', 'application/json');

// Inyección de la colección de usuarios.
$app->users = array(
  1 => array('name' => 'Juan', 'last_name' => 'Ramírez', 'document' => '1094891516'),
  2 => array('name' => 'Daniel', 'last_name' => 'Arbelaez', 'document' => '1094673845' ),
  3 => array('name' => 'José', 'last_name' => 'Ortiz', 'document' => '1094627938' ),
  4 => array('name' => 'Carlos', 'last_name' => 'Ariza', 'document' => '1090341289' ),
  5 => array('name' => 'Yamit', 'last_name' => 'Ospina', 'document' => '1087649032' ));

// Servicio que retorna la colección de usuarios.
$app->get('/users', function() use($app) {
 echo json_encode($app->users);
});

// Servicio que retorna el usuario indicado por parámetro.
$app->get('/users/:id', function($id) use($app) {
  try {
    echo json_encode($app->users[$id]);
  } catch (Exception $e) {
    $app->response->setStatus(404);
  }
});

// Servicio que crea un usuario en el sistema.
$app->post('/users', function() use($app){
  $body = $app->request->getBody();
  $data = json_encode($body, true);
  $id = $data['id']

  $user = array('name' => $data[$id]]['name'],
    'last_name' => $data[$id]['last_name'],
    'document' => $data[$id]['document']);
  $app->users[$data[$id]] = $user;
});

// Servicio para actualizar el usuario indicado por parámetro.
$app->put('/users/:id', function($id) use($app){
  $body = $app->request->getBody();
  $data = json_encode($body, true);

  $user = array('name' => $data['name'],
    'last_name' => $data['last_name'],
    'document' => $data['document']);
  $app->users[$id] = $user;
});

// Servicio para remover el usuario indicado del sistema.
$app->delete('/users/:id', function($id) use($app){
  unset($app->users[$id]);
});

// Se corre la aplicación
$app->run();
 ?>