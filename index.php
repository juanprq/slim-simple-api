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
$app->response->headers->set('Accept', 'application/vnd.test+json; version=1');

// Conexión y referenciación a la colección en base dedatos.
$connection = new MongoClient('localhost');
$app->users = $connection->slim_simple_api->users;

// Método de validación para verificar si son dígitos.
$app->isValidId = $app->container->protect(
  function($value) {
    return preg_match('/^\d+$/', $value);
  });

// Servicio que retorna la colección de usuarios.
$app->get('/users', function() use($app) {
  // Se retorna la colección de usuarios.
  $users_iterator = $app->users->find();
 echo json_encode(iterator_to_array($users_iterator, false), JSON_PRETTY_PRINT);
});

// Servicio que retorna el usuario indicado por parámetro.
$app->get('/users/:id', function($id) use($app) {
  // Se busca un usuario por el identificador que llega por parámetro.
  $user = $app->users->findOne(array('_id' => intval($id)));

  if($user == null) {
    // Si el valor es nulo indica que el recurso no existe.
    $app->response->setStatus(404);
  } else {
    // Si el usuario existe se pone en formato JSON y se retorna como contenido.
    echo json_encode($user, JSON_PRETTY_PRINT);
  }
});

// Servicio que crea un usuario en el sistema.
$app->post('/users', function() use($app){
  // Se referencia el cuerpo del mensaje.
  $body = $app->request->getBody();
  $data = json_decode($body, true);

  // Se referencia el closure para realizar la validación.
  $validator = $app->isValidId;

  if($validator($data['_id'])) {
    // Se crea el arreglo asociativo con los campos requeridos.
    $user = array(
      '_id' => intval($data['_id']),
      'name' => $data['name'],
      'last_name' => $data['last_name'],
      'document' => $data['document']);

    try {
      // Se trata de insertar un usuario a la base de datos.
      $app->users->insert($user);
        
      // Se referencia donde quedó el recurso en la cabecera Location.
      $app->response->headers->set('Location', '/users/' . $data['_id']);
      // Se responde el código de status apropiado.
      $app->response->setStatus(201);
      echo json_encode($user, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
      // En caso de no poder ingresar el registro por ser repetido se resonde el código apropiado.
      $app->response->setStatus(409);
    }
  } else {
    $app->response->setStatus(422);
  }
});

// Servicio para actualizar el usuario indicado por parámetro.
$app->put('/users/:id', function($id) use($app){
  // Se referencia el cuerpo del mensaje.
  $body = $app->request->getBody();
  // Se parsea el contenido JSON a un arreglo asociativo en php.
  $data = json_decode($body, true);

  // Se referencian los valores necesarios en la variable user.
  $user = array('name' => $data['name'],
    'last_name' => $data['last_name'],
    'document' => $data['document']);

  $app->users->update(array('_id' => intval($id)), array('$set' => $user));
  $user['_id'] = intval($id);
  echo json_encode($user, JSON_PRETTY_PRINT);
});

// Servicio para remover el usuario indicado del sistema.
$app->delete('/users/:id', function($id) use($app){
  // Se castea el parámetro a entero
  $id = intval($id);
  // Se trata de cargar el usuario desde base de datos.
  $user = $app->users->findOne(array('_id' => $id));

  if($user == null) {
    // Si el usuario no existe se retorna un 404.
    $app->response->setStatus(404);
  } else {
    // Si el usuario existe se remueve y se retorna la representación del objeto que fué eliminado.
    $app->users->remove(array('_id' => intval($id)));
    echo json_encode($user, JSON_PRETTY_PRINT);
  }
});

// Se corre la aplicación
$app->run();
 ?>