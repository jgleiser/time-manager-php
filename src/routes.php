<?php
include_once '../src/classes/TM/Api.php';
include_once '../src/classes/TM/User.php';
include_once '../src/classes/TM/Manager.php';
include_once '../src/classes/TM/Admin.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \TM\User as User;
use \TM\Manager as Manager;
use \TM\Admin as Admin;

// get template
$app->get('/', function (Request $request, Response $response) {
    $response = $this->view->render($response, "index.html");
    return $response;
});

// Api routes
$app->group('/api', function() {
    
    // Api Version
    $this->group('/v1', function() {
        
        // login
        $this->post('/login', function (Request $request, Response $response) {
            $post_data = $request->getParsedBody() ? $request->getParsedBody() : $request->getQueryParams();
            
            // username and password not provided
            if (!isset($post_data['username']) || !isset($post_data['password'])) {
                $response->withJson(array(
                    'error' => ['msg' => 'username and password required']
                ));
                return $response->withStatus(401);
            }
            
            $userdata = User::login($post_data['username'], $post_data['password']);
            
            // if there is any error on userdata
            if (isset($userdata['error'])) {
                $response->withJson($userdata);
                if (isset($userdata['error']['code'])) {
                    return $response->withStatus($userdata['error']['code']);
                }
                return $response->withStatus(503);
            }
            
            if (isset($_SESSION) && $userdata['id'] > 0) {
                $_SESSION['TM']['userid'] = $userdata['id'];
                $_SESSION['TM']['username'] = $userdata['username'];
                $_SESSION['TM']['role'] = $userdata['role'];
                $_SESSION['TM']['apikey'] = $userdata['apiKey'];
                $_SESSION['TM']['apiKeyExpiration'] = $userdata['apiKeyExpiration'];
            }
            
            $response->withJson($userdata);
            
            return $response;
        })->setName('login');
        
        // Create user, default to USER profile
        $this->post('/users', function (Request $request, Response $response) {
            $post_data = $request->getParsedBody() ? $request->getParsedBody() : $request->getQueryParams();
            
            // username or password not provided
            if (!isset($post_data['username']) || !isset($post_data['password'])) {
                $response->withJson(array(
                    'error' => ['msg' => 'username and password required']
                ));
                return $response;
            }
            
            // username min length
            if (strlen($post_data['username']) < 2) {
                $response->withJson(array(
                    'error' => ['msg' => 'Desired username must have at least 2 characters']
                ));
                return $response;
            }
            
            // password min length
            if (strlen($post_data['password']) < 4) {
                $response->withJson(array(
                    'error' => ['msg' => 'Desired password must have at least 4 characters']
                ));
                return $response;
            }
            
            // create user
            $create = User::create($post_data['username'], $post_data['password']);
            
            // check for errors
            if (isset($create['error'])) {
                $response->withJson($create);
                if (isset($create['error']['code'])) {
                    return $response->withStatus($create['error']['code']);
                }
                return $response->withStatus(503);
            }
            
            $response->withJson(array(
                'msg' => 'User created',
                'userid' => $create
            ));
            
            return $response->withStatus(201);
        })->setName('user-create');
        
        // user data given an id
        $this->map(['GET', 'PUT', 'DELETE'], '/users/{id}', function (Request $request, Response $response, $args) {
            $method = $request->getMethod();
            $data = $method === 'GET' ? $request->getQueryParams() : $request->getParsedBody();
            
            if (!isset($data['apikey'])) {
                $response->withJson(array(
                    'error' => [
                        'msg' => 'apikey required'
                    ]
                ));
                return $response->withStatus(401);
            }
            
            $userdata = User::loginApi($args['id'], $data['apikey']);
            
            // errors with userdata
            if (isset($userdata['error'])) {
                $response->withJson($userdata);
                if (isset($userdata['error']['code'])) {
                    return $response->withStatus($userdata['error']['code']);
                }
                return $response->withStatus(503);
            }
            
            switch ($method) {
                // get user data
                case 'GET':
                    $response->withJson($userdata);
                    return $response;
                    break;
                // update user data
                case 'PUT':
                    $response->withJson(array(
                        'method' => 'update user'
                    ));
                    return $response->withStatus(200);
                    break;
                // delete user
                case 'DELETE':
                    $response->withJson(array(
                        'method' => 'delete user'
                    ));
                    return $response->withStatus(200);
                    break;
                default:
                    $response->withJson(array(
                        'error' => [
                            'msg' => 'Wrong method, GET, PUT or DELETE available'
                        ]
                    ));
                    return $response->withStatus(405);
            }
            
            
        })->setName('user');
        
    });
    
});
