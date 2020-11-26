<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 04-11-18
 * Time: 14:24
 */

include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt20_week2', 'ddwt20','ddwt20');
$nbr_series = count_series($db);
$nbr_users = count_users($db);
$right_column = use_template('cards');
$template = Array(
    1 => Array(
        'name' => 'Home',
        'url' => '/DDWT20/week2/'
    ),
    2 => Array(
        'name' => 'Overview',
        'url' => '/DDWT20/week2/overview/'
    ),
    3 => Array(
        'name' => 'My Account',
        'url' => '/DDWT20/week2/myaccount/'
    ),
    4 => Array(
        'name' => 'Register',
        'url' => '/DDWT20/week2/register/'
    )
);

/* Landing page */
if (new_route('/DDWT20/week2/', 'get')) {
    $names = get_name($db, 1);
    /* Page info */
    $page_title = 'Home';
    $breadcrumbs = get_breadcrumbs([
        'DDWT20' => na('/DDWT20/', False),
        'Week 2' => na('/DDWT20/week2/', False),
        'Home' => na('/DDWT20/week2/', True)
    ]);
    $navigation = get_navigation($template, 1);

    /* Page content */
    $page_subtitle = 'The online platform to list your favorite series';
    $page_content = 'On Series Overview you can list your favorite series. You can see the favorite series of all Series Overview users. By sharing your favorite series, you can get inspired by others and explore new series.';
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('main');
}

/* Overview page */
elseif (new_route('/DDWT20/week2/overview/', 'get')) {
    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'DDWT20' => na('/DDWT20/', False),
        'Week 2' => na('/DDWT20/week2/', False),
        'Overview' => na('/DDWT20/week2/overview', True)
    ]);
    $navigation = get_navigation($template, 2);

    /* Page content */
    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';
    $left_content = get_serie_table(get_series($db), $db);
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('main');
}

/* Single Serie */
elseif (new_route('/DDWT20/week2/serie/', 'get')) {
    /* Get series from db */
    $current_user = get_current();
    $serie_id = $_GET['serie_id'];
    $serie_info = get_serieinfo($db, $serie_id);
    if ($serie_info['user'] == $current_user){
        $display_buttons = True;
    } else {
        $display_buttons = False;
    }


    /* Page info */
    $page_title = $serie_info['name'];
    $breadcrumbs = get_breadcrumbs([
        'DDWT20' => na('/DDWT20/', False),
        'Week 2' => na('/DDWT20/week2/', False),
        'Overview' => na('/DDWT20/week2/overview/', False),
        $serie_info['name'] => na('/DDWT20/week2/serie/?serie_id='.$serie_id, True)
    ]);
    $navigation = get_navigation($template, 2);

    /* Page content */
    $added_by = get_name($db, $serie_info['user']);
    $page_subtitle = sprintf("Information about %s", $serie_info['name']);
    $page_content = $serie_info['abstract'];
    $nbr_seasons = $serie_info['seasons'];
    $creators = $serie_info['creator'];
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('serie');
}

/* Add serie GET */
elseif (new_route('/DDWT20/week2/add/', 'get')) {
    if (!check_login()) {
        redirect(sprintf('/DDWT20/week2/login/'));
    }
    /* Page info */
    $page_title = 'Add Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT20' => na('/DDWT20/', False),
        'Week 2' => na('/DDWT20/week2/', False),
        'Add Series' => na('/DDWT20/week2/new/', True)
    ]);
    $navigation = get_navigation($template, 3);

    /* Page content */
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of you favorite series.';
    $submit_btn = "Add Series";
    $form_action = '/DDWT20/week2/add/';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('new');
}

/* Add serie POST */
elseif (new_route('/DDWT20/week2/add/', 'post')) {
    if (!check_login()) {
        redirect(sprintf('/DDWT20/week2/login/'));
    }
    /* Add serie to database */
    $feedback = add_serie($db, $_POST);
    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT20/week2/add/?error_msg=%s',
        urlencode(json_encode($feedback))));
}

/* Edit serie GET */
elseif (new_route('/DDWT20/week2/edit/', 'get')) {
    if (!check_login()) {
        redirect(sprintf('/DDWT20/week2/login/'));
    }
    /* Get serie info from db */
    $serie_id = $_GET['serie_id'];
    $serie_info = get_serieinfo($db, $serie_id);

    /* Page info */
    $page_title = 'Edit Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT20' => na('/DDWT20/', False),
        'Week 2' => na('/DDWT20/week2/', False),
        sprintf("Edit Series %s", $serie_info['name']) => na('/DDWT20/week2/new/', True)
    ]);
    $navigation = get_navigation($template, 0);

    /* Page content */
    $page_subtitle = sprintf("Edit %s", $serie_info['name']);
    $page_content = 'Edit the series below.';
    $submit_btn = "Edit Series";
    $form_action = '/DDWT20/week2/edit/';

    /* Choose Template */
    include use_template('new');
}

/* Edit serie POST */
elseif (new_route('/DDWT20/week2/edit/', 'post')) {
    if (!check_login()) {
        redirect(sprintf('/DDWT20/week2/login/'));
    }
    $feedback = update_serie($db, $_POST);
    $serie_id = $_POST['serie_id'];
    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT20/week2/serie/?serie_id=%s&error_msg=%s',
        urlencode(json_encode(intval($serie_id))), urlencode(json_encode($feedback))));
}

/* Remove serie */
elseif (new_route('/DDWT20/week2/remove/', 'post')) {
    if (!check_login()) {
        redirect(sprintf('/DDWT20/week2/login/'));
    }
    $serie_id = $_POST['serie_id'];
    $feedback = remove_serie($db, $serie_id);
    $error_msg = get_error($feedback);
    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT20/week2/overview/?serie_id=%s&error_msg=%s',
        urlencode(json_encode(intval($serie_id))), urlencode(json_encode($feedback))));

}
/* User Authentication*/
elseif (new_route('/DDWT20/week2/myaccount/', 'get')) {
    /* Page info */
    if (!check_login()) {
        redirect(sprintf('/DDWT20/week2/login/'));
            }
    $user = get_username($db, $_SESSION['user_id']);
    $page_title = 'My account';
    $breadcrumbs = get_breadcrumbs([
        'DDWT20' => na('/DDWT20/', False),
        'Week 2' => na('/DDWT20/week2/', False),
        'My Account' => na('/DDWT20/week2/myaccount/', True)
    ]);
    $navigation = get_navigation($template, 3);

    /* Page content */
    $page_subtitle = 'Here you can view your favorite series';
    $page_content = 'Welcome to series overview, here you can share your favorite series!';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('account');
}

/* register get */
elseif (new_route('/DDWT20/week2/register/', 'get')){
    /* Page info */
    $page_title = 'Register';
    $breadcrumbs = get_breadcrumbs([
        'DDWT20' => na('/DDWT20/', False),
        'Week 2' => na('/DDWT20/week2/', False),
        'Register' => na('/DDWT20/week2/register/', True)
    ]);
    $navigation = get_navigation($template, 4);
    /* Page content */
    $page_subtitle = 'Register on Series Overview!';
    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) { $error_msg = get_error($_GET['error_msg']); }
    /* Choose Template */
    include use_template('register');
}

/* register post */
elseif (new_route('/DDWT20/week2/register/', 'post')) {
    $error_msg = register_user($db, $_POST);
    redirect(sprintf('/DDWT20/week2/register/?error_msg=%s',
        urlencode(json_encode($error_msg))));

}

/* login get */
elseif (new_route('/DDWT20/week2/login/', 'get')){
    if (check_login()) {
        redirect(sprintf('/DDWT20/week2/myaccount/'));
    }
    /* Page info */
    $page_title = 'Login';
    $breadcrumbs = get_breadcrumbs([
    'DDWT20' => na('/DDWT20/', False),
    'Week 2' => na('/DDWT20/week2/', False),
    'Login' => na('/DDWT20/week2/login/', True)
    ]);
    $navigation = get_navigation($template, 0);
    /* Page content */
    $page_subtitle = 'Use your username and password to login';
    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) { $error_msg = get_error($_GET['error_msg']); }
    /* Choose Template */
    include use_template('login');
    }

/* login post */
elseif (new_route('/DDWT20/week2/login/', 'post')) {
    $error_msg = login_user($db, $_POST);
    redirect(sprintf('/DDWT20/week2/login/?error_msg=%s',
        urlencode(json_encode($error_msg))));
}

/* logout get */
elseif (new_route('/DDWT20/week2/logout/', 'get')) {
    session_destroy();
    $error_msg = logout_user();
    redirect(sprintf('/DDWT20/week2/?error_msg=%s',
        urlencode(json_encode($error_msg))));
}
