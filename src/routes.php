<?php
include_once '../src/classes/TM/Api.php';
include_once '../src/classes/TM/User.php';
include_once '../src/classes/TM/Manager.php';
include_once '../src/classes/TM/Admin.php';
include_once '../src/classes/TM/TimeNote.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \TM\User as User;
use \TM\Manager as Manager;
use \TM\Admin as Admin;
use \TM\TimeNote as TimeNote;

// app template
$app->get('/', function (Request $request, Response $response) {
    $response = $this->view->render($response, "index.html");
    return $response;
});

// app logout
$app->get('/logout', function (Request $request, Response $response) {
    unset($_SESSION['TM']['userid']);
    unset($_SESSION['TM']['username']);
    unset($_SESSION['TM']['role']);
    unset($_SESSION['TM']['apikey']);
    unset($_SESSION['TM']['apiKeyExpiration']);
    $response->withJson(['msg' => 'User loged out']);
    return $response;
});

// Api routes
$app->group('/api', function() {
    
    // Api Version
    $this->group('/v1', function() {
        
        // login
        $this->post('/login', function (Request $request, Response $response) {
            $post_data = $request->getParsedBody();
            
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
                $http_status = isset($userdata['error']['code']) ? $userdata['error']['code'] : 503;
                return $response->withStatus($http_status);
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
            $post_data = $request->getParsedBody();
            
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
                $http_status = isset($create['error']['code']) ? $create['error']['code'] : 503;
                return $response->withStatus($http_status);
            }
            
            $response->withJson(array(
                'msg' => 'User created',
                'userid' => $create
            ));
            
            return $response->withStatus(201);
        })->setName('user-create');
        
        /*  user data given an id
        *   GET: show user data
        *   PUT: update user data, password or work hours
        *   DELETE: delete user
        */
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
                $http_status = isset($userdata['error']['code']) ? $userdata['error']['code'] : 503;
                return $response->withStatus($http_status);
            }
            
            $currentUser = new User($userdata);
            
            switch ($method) {
                // get user data
                case 'GET':
                    $response->withJson($currentUser->show());
                    return $response;
                    break;
                // update user data
                case 'PUT':
                    // change password
                    if (isset($data['oldPwd']) && isset($data['newPwd'])) {
                        if (strlen($data['newPwd']) > 3) {
                            $upd = $currentUser->update($data['oldPwd'], $data['newPwd']);
                        } else {
                            $response->withJson(array(
                                'error' => ['msg' => "Your new password has to be at least 4 characters long."]
                            ));
                            return $response;
                        }
                    } else if (isset($data['start_time']) || isset($data['end_time'])) {
                        // change start and end working time
                        $newStart = isset($data['start_time']) ? $data['start_time'] : NULL;
                        $newEnd = isset($data['end_time']) ? $data['end_time'] : NULL;
                        if ($newStart && $currentUser->getWorkHoursStart() !== $newStart ||
                            $newEnd && $currentUser->getWorkHoursEnd() !== $newEnd) {
                            if ($newStart) $currentUser->setWorkHoursStart($newStart);
                            if ($newEnd) $currentUser->setWorkHoursEnd($newEnd);
                            $upd = $currentUser->update();
                        } else {
                            $response->withJson(array(
                                'msg' => "You selected same working hours that you had, update not needed."
                            ));
                            return $response;
                        }
                    } else {
                        $response->withJson(array(
                            'msg' => "You need to provide either newPwd and oldPwd to change your password or start_time and-or end_time to change your working hours"
                        ));
                        return $response;
                    }
                    // $upd not set, unexpected error
                    if (!isset($upd)) {
                        $response->withJson(array(
                            'error' => ['msg' => 'Unexpected error updating user']
                        ));
                        return $response->withStatus(503);
                    }
                    // errors with $upd or not updated
                    if (isset($upd['error']) || true !== $upd) {
                        $response->withJson($upd);
                        $http_status = isset($upd['error']['code']) ? $upd['error']['code'] : 503;
                        return $response->withStatus($http_status);
                    }
                    if (isset($data['oldPwd']) && isset($data['newPwd'])) {
                        $response->withJson(array(
                            'msg' => 'User password updated'
                        ));
                    } else {
                        $response->withJson(array(
                            'msg' => 'User work hours updated'
                        ));
                    }
                    return $response;
                    break;
                // delete user
                case 'DELETE':
                    if (!isset($data['password'])) {
                        $response->withJson(array(
                            'error' => ['msg' => "user password required to delete account"]
                        ));
                        return $response->withStatus(401);
                    }
                    $deleted = $currentUser->delete($data['password']);
                    if (isset($deleted['error'])) {
                        $response->withJson($deleted);
                        $http_status = isset($deleted['error']['code']) ? $deleted['error']['code'] : 503;
                        return $response->withStatus($http_status);
                        
                    }
                    $response->withJson($deleted);
                    return $response->withStatus(204);
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
        
        /*  Timenotes
        *   GET: show all timenotes from user 
        *   POST: create new timenote for user
        *   DELETE: delete all timenotes from user
        */
        $this->map(['GET', 'POST', 'DELETE'], '/users/{userid}/timenotes', function (Request $request, Response $response, $args) {
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
            
            $userdata = User::loginApi($args['userid'], $data['apikey']);
            
            // errors with userdata
            if (isset($userdata['error'])) {
                $response->withJson($userdata);
                $http_status = isset($userdata['error']['code']) ? $userdata['error']['code'] : 503;
                return $response->withStatus($http_status);
            }
            
            $currentUser = new User($userdata);
            
            switch ($method) {
                // show all user timenotes
                case 'GET':
                    $response->withJson(array(
                        'method' => 'GET',
                        'msg' => 'Not implemented yet'
                    ));
                    return $response;
                    break;
                case 'POST':
                    $start_dt = isset($data['start_dt']) ? $data['start_dt'] : NULL;
                    $start_time = isset($data['start_time']) ? $data['start_time'] : NULL;
                    $work_hours = isset($data['work_hours']) ? $data['work_hours'] : NULL;
                    $time_note = isset($data['time_note']) ? $data['time_note'] : NULL;
                    $note = new TimeNote($currentUser);
                    $note->setStartDate($start_dt . " " . $start_time)
                         ->setWorkHours($work_hours)
                         ->setNote($time_note);
                    $create = $note->create();
                    // errors in create
                    if (isset($create['error'])) {
                        $response->withJson($create);
                        $http_status = isset($create['error']['code']) ? $create['error']['code'] : 503;
                        return $response->withStatus($http_status);
                    }
                    $response->withJson(array(
                        'msg' => 'Timenote created',
                        'noteid' => $note->getId()
                    ));
                    return $response->withStatus(201);
                    break;
                case 'DELETE':
                    $response->withJson(array(
                        'method' => 'DELETE',
                        'msg' => 'Not implemented yet'
                    ));
                    return $response;
                    break;
                default:
                    $response->withJson(array(
                        'error' => [
                            'msg' => 'Wrong method, GET, POST or DELETE available'
                        ]
                    ));
                    return $response->withStatus(405);
            }
            return $response;
        })->setName('timenotes');
        
        /*  Timenotes by id
        *   GET: show user timenote
        *   PUT: update a given timenote
        *   DELETE: delete a timenote
        */
        $this->map(['GET', 'PUT', 'DELETE'], '/users/{userid}/timenotes/{noteid}', function (Request $request, Response $response, $args) {
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
            
            $userdata = User::loginApi($args['userid'], $data['apikey']);
            
            // errors with userdata
            if (isset($userdata['error'])) {
                $response->withJson($userdata);
                $http_status = isset($userdata['error']['code']) ? $userdata['error']['code'] : 503;
                return $response->withStatus($http_status);
            }
            
            $currentUser = new User($userdata);
            
            switch ($method) {
                case 'GET':
                    $response->withJson(array(
                        'method' => 'GET',
                        'msg' => 'Not implemented yet'
                    ));
                    return $response;
                    break;
                case 'PUT':
                    $response->withJson(array(
                        'method' => 'PUT',
                        'msg' => 'Not implemented yet'
                    ));
                    return $response;
                    break;
                case 'DELETE':
                    $response->withJson(array(
                        'method' => 'DELETE',
                        'msg' => 'Not implemented yet'
                    ));
                    return $response;
                    break;
                default:
                    $response->withJson(array(
                        'error' => [
                            'msg' => 'Wrong method, GET, PUT or DELETE available'
                        ]
                    ));
                    return $response->withStatus(405);
            }
            return $response;
        })->setName('timenote');
        
    });
    
});
