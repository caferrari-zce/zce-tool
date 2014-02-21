<?php

ini_set('error_reporting', -1);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

chdir(__DIR__);

@mkdir(getcwd() . '/cache/');

$loader = new Twig_Loader_Filesystem(getcwd() . '/view/');
$twig = new Twig_Environment($loader, array(
    //'cache' => getcwd() . '/cache/',
));

$twig->addFilter(new Twig_SimpleFilter('toChar', function ($index) {
    $letters = range('A', 'Z');
    return $letters[(int)$index];
}));

$twig->addFilter(new Twig_SimpleFilter('implode', function ($array) use ($twig) {
    $filter = $twig->getFilter('toChar')->getCallable();

    if (!is_array($array)) {
        return $array;
    }

    $array = array_map(function($item) use ($filter) {
        return $filter($item);
    }, $array);

    return implode(', ', $array);
}));

use Respect\Rest\Router;

$router = new Router;

$homeRoute = $router->get('/', function() use ($twig) {

    $files = glob('questions/*.json');

    $questions = array();

    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file));

        $questions[] = (object)array(
            'question' => $data->question,
            'type' => $data->type,
            'category' => $data->category,
            'id' => substr($file, 10, 40),
            'short_id' => substr($file, 10, 10)
        );
    }

    return $twig->render('index.twig', array('questions' => $questions));
});

$addRoute = $router->get('/question/add', function() use ($twig) {
    return $twig->render('add.twig');
});

$viewRoute = $router->get('/question/*', function($id) use ($twig) {
    $data = array(
        'question' => json_decode(file_get_contents(getcwd() . "/questions/{$id}.json")),
        'id' => $id
    );

    return $twig->render('render.twig', $data);
});

$router->post('/question/add', function() use ($twig, $homeRoute) {

    $question = (object)array(
        'question' => trim($_POST['question']),
        'category' => trim($_POST['category'])
    );

    $types = array(
        'c' => 'choice',
        't' => 'text',
        'f' => 'fill'
    );

    $question->type = $types[$_POST['type']];

    if ($_POST['code']) {
        $question->code = $_POST['code'];
    }

    if ($question->type == 'text') {
        $question->answer = trim($_POST['text_answer']);
    } elseif ($question->type == 'fill') {
        $question->phrase = trim($_POST['phrase']);
        $question->answer = trim($_POST['fill_answer']);
    } elseif ($question->type == 'choice') {
        $question->alternatives = array_filter($_POST['alternative'], function($alternative) {
            return (bool)strlen($alternative);
        });

        $question->alternatives = array_map('trim', $question->alternatives);

        $question->answer = array_keys(array_filter($_POST['correct'], function($correct) {
            return 'y' ==  $correct;
        }));
    }

    $question->hash = sha1(serialize($question));

    $contents = json_encode($question, JSON_PRETTY_PRINT);
    $id = sha1($question->question);

    file_put_contents(getcwd() . "/questions/{$id}.json", $contents);

    return $homeRoute;
});
