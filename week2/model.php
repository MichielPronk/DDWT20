<?php
/**
 * Model
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Connects to the database using PDO
 * @param string $host database host
 * @param string $db database name
 * @param string $user database user
 * @param string $pass database password
 * @return pdo object
 */
function connect_db($host, $db, $user, $pass){
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        echo sprintf("Failed to connect. %s",$e->getMessage());
    }
    return $pdo;
}

/**
 * Check if the route exist
 * @param string $route_uri URI to be matched
 * @param string $request_type request method
 * @return bool
 *
 */
function new_route($route_uri, $request_type){
    $route_uri_expl = array_filter(explode('/', $route_uri));
    $current_path_expl = array_filter(explode('/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if ($route_uri_expl == $current_path_expl && $_SERVER['REQUEST_METHOD'] == strtoupper($request_type)) {
        return True;
    }
}

/**
 * Creates a new navigation array item using url and active status
 * @param string $url The url of the navigation item
 * @param bool $active Set the navigation item to active or inactive
 * @return array
 */
function na($url, $active){
    return [$url, $active];
}

/**
 * Creates filename to the template
 * @param string $template filename of the template without extension
 * @return string
 */
function use_template($template){
    $template_doc = sprintf("views/%s.php", $template);
    return $template_doc;
}

/**
 * Creates breadcrumb HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs) {
    $breadcrumbs_exp = '
    <nav aria-label="breadcrumb">
    <ol class="breadcrumb">';
    foreach ($breadcrumbs as $name => $info) {
        if ($info[1]){
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">'.$name.'</li>';
        }else{
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="'.$info[0].'">'.$name.'</a></li>';
        }
    }
    $breadcrumbs_exp .= '
    </ol>
    </nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation HTML code using given array
 * @param array $navigation Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the navigation
 */
function get_navigation($template, $active_id){
    $navigation_exp = '
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand">Series Overview</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">';
    foreach ($template as $name => $info) {
        if ($name == $active_id){
            $navigation_exp .= '<li class="nav-item active">';
            $navigation_exp .= '<a class="nav-link" href="'.$template[$name]['url'].'">'.$template[$name]['name'].'</a>';
        }else{
            $navigation_exp .= '<li class="nav-item">';
            $navigation_exp .= '<a class="nav-link" href="'.$template[$name]['url'].'">'.$template[$name]['name'].'</a>';
        }

        $navigation_exp .= '</li>';
    }
    $navigation_exp .= '
    </ul>
    </div>
    </nav>';
    return $navigation_exp;
}

/**
 * Creates a Bootstrap table with a list of series
 * @param array $series with series from the db
 * @return string
 */
function get_serie_table($series, $pdo){
    $table_exp = '
    <table class="table table-hover">
    <thead
    <tr>
        <th scope="col">Series</th>
        <th scope="col"></th>
    </tr>
    </thead>
    <tbody>';
    foreach($series as $key => $value){
        $table_exp .= '
        <tr>
            <th scope="row">'.$value['name'].'</th>
            <th scope="row">'.get_name($pdo, $value['user']).'</th>
            <td><a href="/DDWT20/week2/serie/?serie_id='.$value['id'].'" role="button" class="btn btn-primary">More info</a></td>
        </tr>
        ';
    }
    $table_exp .= '
    </tbody>
    </table>
    ';
    return $table_exp;
}

/**
 * Pretty Print Array
 * @param $input
 */
function p_print($input){
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Get array with all listed series from the database
 * @param object $pdo database object
 * @return array Associative array with all series
 */
function get_series($pdo){
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->fetchAll();
    $series_exp = Array();

    /* Create array with htmlspecialchars */
    foreach ($series as $key => $value){
        foreach ($value as $user_key => $user_input) {
            $series_exp[$key][$user_key] = htmlspecialchars($user_input);
        }
    }
    return $series_exp;
}

/**
 * Generates an array with serie information
 * @param object $pdo db object
 * @param int $serie_id id from the serie
 * @return mixed
 */
function get_serieinfo($pdo, $serie_id){
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$serie_id]);
    $serie_info = $stmt->fetch();
    $serie_info_exp = Array();

    /* Create array with htmlspecialchars */
    foreach ($serie_info as $key => $value){
        $serie_info_exp[$key] = htmlspecialchars($value);
    }
    return $serie_info_exp;
}

/**
 * Creates HTML alert code with information about the success or failure
 * @param array $feedback Array with keys 'type' and 'message'.
 * @return string
 */
function get_error($feedback){
    $feedback = json_decode($feedback, True);
    $error_exp = '
        <div class="alert alert-'.$feedback['type'].'" role="alert">
            '.$feedback['message'].'
        </div>';
    return $error_exp;
}

/**
 * Add serie to the database
 * @param object $pdo db object
 * @param array $serie_info post array
 * @return array with message feedback
 */
function add_serie($pdo, $serie_info){
    /* Check if all fields are set */

    if (
        empty($serie_info['Name']) or
        empty($serie_info['Creator']) or
        empty($serie_info['Seasons']) or
        empty($serie_info['Abstract'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }


    /* Check data type */
    $user_id = $_SESSION['user_id'];
    if (!is_numeric($serie_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    /* Check if serie already exists */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$serie_info['Name']]);
    $serie = $stmt->rowCount();
    if ($serie){
        return [
            'type' => 'danger',
            'message' => 'This series was already added.'
        ];
    }
    $stmt = $pdo->prepare("SELECT MAX(id) from series");
    $stmt->execute();
    $max_id = $stmt -> fetch();
    $max_id = htmlspecialchars((implode('|', $max_id)));
    /* Add Serie */
    $stmt = $pdo->prepare("INSERT INTO series (id, name, creator, seasons, abstract, user) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        (int) $max_id + 1,
        $serie_info['Name'],
        $serie_info['Creator'],
        $serie_info['Seasons'],
        $serie_info['Abstract'],
        $user_id
    ]);
    $inserted = $stmt->rowCount();
    if ($inserted ==  1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' added to Series Overview.", $serie_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series was not added. Try it again.'
        ];
    }
}

/**
 * Updates a serie in the database using post array
 * @param object $pdo db object
 * @param array $serie_info post array
 * @return array
 */
function update_serie($pdo, $serie_info){
    /* Check if all fields are set */
    if ($serie_info['user'] == $_SESSION['user_id']){
        $display_buttons = True;
    } else {
        $display_buttons = False;
    }

    if (
        empty($serie_info['Name']) or
        empty($serie_info['Creator']) or
        empty($serie_info['Seasons']) or
        empty($serie_info['Abstract']) or
        empty($serie_info['serie_id'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    $user_id = $_SESSION['user_id'];

    /* Check data type */
    if (!is_numeric($serie_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    /* Get current series name */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$serie_info['serie_id']]);
    $serie = $stmt->fetch();
    $current_name = $serie['name'];

    /* Check if serie already exists */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$serie_info['Name']]);
    $serie = $stmt->fetch();
    if ($serie_info['Name'] == $serie['name'] and $serie['name'] != $current_name){
        return [
            'type' => 'danger',
            'message' => sprintf("The name of the series cannot be changed. %s already exists.", $serie_info['Name'])
        ];
    }
    if($display_buttons == True){
    /* Update Serie */
    $stmt = $pdo->prepare("UPDATE series SET name = ?, creator = ?, seasons = ?, abstract = ? WHERE id = ?");
    $stmt->execute([
        $serie_info['Name'],
        $serie_info['Creator'],
        $serie_info['Seasons'],
        $serie_info['Abstract'],
        $serie_info['serie_id']
    ]);
    $updated = $stmt->rowCount();
    if ($updated ==  1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was edited!", $serie_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'The series was not edited. No changes were detected.'
        ];
    }} else {
        return [
            'type' => 'warning',
            'message' => 'You are not authorized to edit this series'
        ];
    }
}

/**
 * Removes a series with a specific series-ID
 * @param object $pdo db object
 * @param int $serie_id id of the to be deleted series
 * @return array
 */
function remove_serie($pdo, $serie_id)
{
    $serie_info = get_serieinfo($pdo, $serie_id);
    if ($serie_info['user'] == $_SESSION['user_id']) {
        $display_buttons = True;
    } else {
        $display_buttons = False;
    }


    /* Delete Serie */
    if ($display_buttons == True) {
        $stmt = $pdo->prepare("DELETE FROM series WHERE id = ?");
        $stmt->execute([$serie_id]);
        $deleted = $stmt->rowCount();
        if ($deleted == 1) {
            return [
                'type' => 'success',
                'message' => sprintf("Series '%s' was removed!", $serie_info['name'])
            ];
        }
         else {
            return [
                'type' => 'warning',
                'message' => 'An error occurred. The series was not removed.'
            ];
        }
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'You are not authorized to remove this series'
        ];
    }
}

/**
 * Count the number of series listed on Series Overview
 * @param object $pdo database object
 * @return mixed
 */
function count_series($pdo){
    /* Get series */
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->rowCount();
    return $series;
}

/**
 * Changes the HTTP Header to a given location
 * @param string $location location to be redirected to
 */
function redirect($location){
    header(sprintf('Location: %s', $location));
    die();
}

/**
 * Get current user id
 * @return bool current user id or False if not logged in
 */
function get_user_id(){
    session_start();
    if (isset($_SESSION['user_id'])){
        return $_SESSION['user_id'];
    } else {
        return False;
    }
}

/**
 * @param $pdo database
 * @param $user_id string user id of the creator of a series
 * @return string containing the first and lastname of the user
 */
function get_name($pdo, $user_id){
    $stmt = $pdo->prepare('SELECT firstname, lastname FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user_name = $stmt->fetch();
    $name = '';
    foreach ($user_name as $value){
        $name.=$value.' ';
    }
    return $name;
}

/**
 * @param $pdo database
 * @return mixed the number of users in the database
 */

function count_users($pdo){
    $stmt = $pdo->prepare('SELECT * FROM users');
    $stmt->execute();
    $users = $stmt->rowCount();
    return $users;
}

/**
 * @param $pdo
 * @param $form_data array containing the information of a new user
 * @return array|string[] the error type and message for the error
 */

function register_user($pdo, $form_data){
    /* Check if all fields are set */
    if (
        empty($form_data['username']) or
        empty($form_data['password']) or
        empty($form_data['firstname']) or
        empty($form_data['lastname'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'You should enter a username, password, first- and last name.'
        ];
    }
    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$form_data['username']]);
        $user_exists = $stmt->rowCount();
    } catch (\PDOException $e) {
        return [
            'type' => 'danger',
            'message' => sprintf('There was an error: %s', $e->getMessage())
        ];
    }
    /* Return error message for existing username */
    if ( !empty($user_exists) ) {
        return [
            'type' => 'danger',
            'message' => 'The username you entered does already exists!'
        ];
    }
    /* Hash password */
    $password = password_hash($form_data['password'], PASSWORD_DEFAULT);
    /* Save user to the database */
    $stmt = $pdo->prepare("SELECT MAX(id) from users");
    $stmt->execute();
    $max_id = $stmt -> fetch();
    $max_id = htmlspecialchars((implode('|', $max_id)));
    $user_id = (int) $max_id + 1;
    try {
        $stmt = $pdo->prepare('INSERT INTO users (id, username, password, firstname,lastname) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $user_id,
            $form_data['username'],
            $password,
            $form_data['firstname'],
            $form_data['lastname']]);
    } catch (PDOException $e) {
        return [
            'type' => 'danger',
            'message' => sprintf('There was an error: %s', $e->getMessage())
        ];
    }

    session_start();
    $_SESSION['user_id'] = $user_id;
    $feedback = [
        'type' => 'success',
        'message' => sprintf('%s, your account was successfully created!',  htmlspecialchars(get_username($pdo, $_SESSION['user_id'])))
    ];
    redirect(sprintf('/DDWT20/week2/myaccount/?error_msg=%s',
        json_encode($feedback)));
}

/**
 * @param $pdo database
 * @param $form_data array containing login information of a user
 * @return array|string[] the error type and message for the error
 */

function login_user($pdo, $form_data){
    if (
        empty($form_data['username']) or
        empty($form_data['password'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'You should enter a username and password.'
        ];
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$form_data['username']]);
        $user_info = $stmt->fetch();
    } catch (\PDOException $e) {
        return [
            'type' => 'danger',
            'message' => sprintf('There was an error: %s', $e->getMessage())
        ];
    }
    /* Return error message for wrong username */
    if ( empty($user_info) ) {
        return [
            'type' => 'danger',
            'message' => 'The username you entered does not exist!'
        ];
    }
    if ( !password_verify($form_data['password'], $user_info['password']) ){
        return [
            'type' => 'danger',
            'message' => 'The password you entered is incorrect!'
        ];
    } else {
        session_start();
        $_SESSION['user_id'] = $user_info['id'];
        $feedback = [
            'type' => 'success',
            'message' => sprintf('%s, you were logged in successfully!',
                htmlspecialchars($form_data['username']))
        ];
        redirect(sprintf('/DDWT20/week2/myaccount/?error_msg=%s',
            json_encode($feedback)));
    }
}

/**
 * @param $pdo database
 * @param $id string the id of a user whose username we want
 * @return string the username
 */

function get_username($pdo, $id){
    $stmt = $pdo->prepare('SELECT username FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $username = $stmt->fetch();
    $username_string = '';
    foreach ($username as $value){
        $username_string.=$value;
    }
    return htmlspecialchars($username_string);
}

/**
 * @return bool
 */

function check_login(){
    session_start();
    if (isset($_SESSION['user_id'])){
        return True;
    } else {
        return False;
    }
}

/**
 * @return int|mixed returns the id of the active user if not active it returns 0
 */

function get_current(){
    if (check_login()){
        return $_SESSION['user_id'];
    } else {
        return 0;
    }
}

/**
 * @return string[] the error type and message for the error
 */

function logout_user(){
    session_destroy();
    session_unset();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    if (isset($_SESSION['user_id'])){
        return [
            'type' => 'danger',
            'message' => 'You were not logged out'
            ];
    }
    return [
        'type' => 'success',
        'message' => 'You we succesfully logged out'
    ];
}

