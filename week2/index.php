<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

include 'model.php';

session_start();

/* Connect to DB */
$db = connect_db('localhost', 'ddwt18_week2', 'ddwt18','ddwt18');
/* Redundant Code */
/* Get Number of Series */
$nbr_series = count_series($db);
$nbr_users = count_users($db);
$right_column = use_template('cards');
$template = Array(
    1 => Array(
        'name' => 'Home',
        'url' => '/DDWT18/week2/'
    ),
    2 => Array(
        'name' => 'Overview',
        'url' => '/DDWT18/week2/overview/'
    ),
    3 => Array(
        'name' => 'My Account',
        'url' => '/DDWT18/week2/myaccount/'
    ),
    4 => Array(
        'name' => 'Register',
        'url' => '/DDWT18/week2/register/'
    ));


/* Landing page */
if (new_route('/DDWT18/week2/', 'get')) {
    /* Page info */
    $page_title = 'Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Home' => na('/DDWT18/week2/', True)
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
elseif (new_route('/DDWT18/week2/overview/', 'get')) {
    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Overview' => na('/DDWT18/week2/overview', True)
    ]);
    $navigation = get_navigation($template, 2);

    /* Page content */
    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';
    $left_content = get_serie_table(get_series($db), $db);

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('main');
}

/* Single Serie */
elseif (new_route('/DDWT18/week2/serie/', 'get')) {

    /* Get series from db */

    if (empty($_GET['serie_id'])) {
        $serie_id = json_decode($_GET['error_msg'], True)['serie_id'];
    } else {
        $serie_id = $_GET['serie_id'];
    }
    $serie_info = get_serieinfo($db, $serie_id);
    if ($serie_info['user'] == $_SESSION['userid']) {
        $display_buttons = True;
    } else {
        $display_buttons = False;
    }
    /* Page info */
    $page_title = $serie_info['name'];
    $added_by = getName($serie_info['user'], $db);
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Overview' => na('/DDWT18/week2/overview/', False),
        $serie_info['name'] => na('/DDWT18/week2/serie/?serie_id='.$serie_id, True)
    ]);
    $navigation = get_navigation($template, 2);

    /* Page content */
    $page_subtitle = sprintf("Information about %s", $serie_info['name']);
    $page_content = $serie_info['abstract'];
    $nbr_seasons = $serie_info['seasons'];
    $creators = $serie_info['creator'];

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('serie');
}

/* Add serie GET */
elseif (new_route('/DDWT18/week2/add/', 'get')) {
    /* Page info */
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }
    $page_title = 'Add Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Add Series' => na('/DDWT18/week2/new/', True)
    ]);
    $navigation = get_navigation($template, 3);

    /* Page content */
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of you favorite series.';
    $submit_btn = "Add Series";
    $form_action = '/DDWT18/week2/add/';

    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('new');
}

/* Add serie POST */
elseif (new_route('/DDWT18/week2/add/', 'post')) {
    /* Add serie to database */
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }
    $feedback = add_serie($db, $_POST);
    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT18/week2/add/?error_msg=%s',
        json_encode($feedback)));
}

/* Edit serie GET */
elseif (new_route('/DDWT18/week2/edit/', 'get')) {
    /* Get serie info from db */
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }
    $serie_id = $_GET['serie_id'];
    $serie_info = get_serieinfo($db, $serie_id);

    /* Page info */
    $page_title = 'Edit Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        sprintf("Edit Series %s", $serie_info['name']) => na('/DDWT18/week2/new/', True)
    ]);
    $navigation = get_navigation($template, 3);

    /* Page content */
    $page_subtitle = sprintf("Edit %s", $serie_info['name']);
    $page_content = 'Edit the series below.';
    $submit_btn = "Edit Series";
    $form_action = '/DDWT18/week2/edit/';
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('new');
}

/* Edit serie POST */
elseif (new_route('/DDWT18/week2/edit/', 'post')) {
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }
    /* Add serie to database */
    $feedback = update_serie($db, $_POST);
    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT18/week2/serie/?error_msg=%s',
        json_encode($feedback)));

}

/* Remove serie */
elseif (new_route('/DDWT18/week2/remove/', 'post')) {
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }
    /* Remove serie in database */
    $feedback = remove_serie($db, $_POST['serie_id']);
    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT18/week2/overview/?error_msg=%s',
        json_encode($feedback)));
}

elseif (new_route('/DDWT18/week2/myaccount/', 'get')) {
    /* Page info */
    if ( !check_login() ) {
        redirect('/DDWT18/week2/login/');
    }
    $page_title = getName($_SESSION['userid'], $db);

    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'My Account' => na('/DDWT18/week2/myaccount', True)
    ]);
    $navigation = get_navigation($template, 3);

    /* Page content */
    $page_subtitle = 'This is your Account';
    $page_content = 'Here you find all options for your account.';
    $user = getName($_SESSION['userid'], $db);

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('account');
}

elseif (new_route('/DDWT18/week2/register/', 'get')) {
    /* Page info */
    $page_title = 'Register';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Register' => na('/DDWT18/week2/register', True)
    ]);
    $navigation = get_navigation($template, 4);

    /* Page content */
    $page_subtitle = 'Register your account here';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('register');
}

elseif (new_route('/DDWT18/week2/register/', 'post')) {
    /* Register user */
    $error_msg = register_user($db, $_POST);
    /* Redirect to homepage */
    redirect(sprintf('/DDWT18/week2/register/?error_msg=%s',
        json_encode($error_msg)));
}

elseif (new_route('/DDWT18/week2/login/', 'get')) {
    if ( check_login() ) {
        redirect('/DDWT18/week2/myaccount/');
    }
    /* Page info */
    $page_title = 'Login';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Login' => na('/DDWT18/week2/login', True)
    ]);
    $navigation = get_navigation($template, 3);

    /* Page content */
    $page_subtitle = 'Please, login here';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Choose Template */
    include use_template('login');
}

elseif (new_route('/DDWT18/week2/login/', 'post')) {
    /* Register user */
    $error_msg = login_user($db, $_POST);
    /* Redirect to homepage */
    if ($error_msg['type'] == "success") {
        redirect(sprintf('/DDWT18/week2/myaccount/?error_msg=%s',
            json_encode($error_msg)));
    } else {
        redirect(sprintf('/DDWT18/week2/login/?error_msg=%s',
            json_encode($error_msg)));
    }

}

elseif (new_route('/DDWT18/week2/logout/', 'get')) {
    $error_msg = logout_user($db);
    redirect(sprintf('/DDWT18/week2/?error_msg=%s',
        json_encode($error_msg)));
}

else {
    http_response_code(404);
}