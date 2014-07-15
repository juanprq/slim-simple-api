<?php 
// Carga de librerías de composer
require 'vendor/autoload.php';

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

// Se cargan los parámetros iniciales de configuración para slim.
$config = require_once __DIR__ . '/config.php';

// Se inicializa la aplicación slim con las propiedades cargadas del archivo para slim
$app = new \Slim\Slim($config['slim']);

// Se configura la aplicación para que responda en formato JSON.
$app->response->headers->set('Content-Type', 'application/json');

// Conexión y referenciación a la colección en base dedatos.
$connection = new MongoClient('localhost');
$users = $connection->slim_simple_api->users;

// Asignación de variable para simulación de recurso.
// $users = array(
//   1 => array('name' => 'Juan', 'last_name' => 'Ramírez', 'document' => '1094891516'),
//   2 => array('name' => 'Daniel', 'last_name' => 'Arbelaez', 'document' => '1094673845' ),
//   3 => array('name' => 'José', 'last_name' => 'Ortiz', 'document' => '1094627938' ),
//   4 => array('name' => 'Carlos', 'last_name' => 'Ariza', 'document' => '1090341289' ),
//   5 => array('name' => 'Yamit', 'last_name' => 'Ospina', 'document' => '1087649032' ));

// Servicio que retorna la colección de usuarios.
$app->get('/users', function() use($users) {
  // Se retorna la colección de usuarios.
 echo json_encode($users->find());
});

// Servicio que retorna el usuario indicado por parámetro.
$app->get('/users/:id', function($id) use($app, $users) {
  // Se busca un usuario por el identificador que llega por parámetro.
  $user = $users->findOne(array('_id' => $id));

  if($user == null) {
    // Si el valor es nulo indica que el recurso no existe.
    $app->response->setStatus(404);  
  } else {
    // Si el usuario existe se pone en formato JSON y se retorna como contenido.
    echo json_encode($user);
  }
});

// Servicio que crea un usuario en el sistema.
$app->post('/users', function() use($app, $users){
  // Se referencia el cuerpo del mensaje.
  $body = $app->request->getBody();
  $data = json_decode($body, true);

  // Se crea el arreglo asociativo con los campos requeridos.
  $user = array(
    '_id' => $data['_id'],
    'name' => $data['name'],
    'last_name' => $data['last_name'],
    'document' => $data['document']);

  try {
    // Se trata de insertar un usuario a la base de datos.
    $users->insert($user);
      
    // Se referencia donde quedó el recurso en la cabecera Location.
    $app->response->headers->set('Location', '/users/' . $data['_id']);
    // Se responde el código de status apropiado.
    $app->response->setStatus(201);
  } catch (Exception $e) {
    // En caso de no poder ingresar el registro por ser repetido se resonde el código apropiado.
    $app->response->setStatus(409);
  }
});

// Servicio para actualizar el usuario indicado por parámetro.
$app->put('/users/:id', function($id) use($app){
  $body = $app->request->getBody();
  $data = json_decode($body, true);

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