<?php

namespace App\Controllers;

use mysqli;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class DatabaseController
{
    private $view;
    private $logger;

    public function __construct($view, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->logger = $logger;
    }

    public function dispatch(Request $request, Response $response, $args)
    {
        $this->logger->info("Database action dispatched");

        $mysqli = new mysqli("localhost", "root", "", "bank-test");
        if ($mysqli->connect_errno) {
            echo "Echec lors de la connexion à MySQL : (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }

        $sql="SELECT * FROM requests";
        $result_req=mysqli_query($mysqli, $sql);
        $requests = mysqli_fetch_all($result_req, MYSQLI_ASSOC);


        $sql="SELECT * FROM responses";
        $result=mysqli_query($mysqli, $sql);
        $responses = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $data = [
            'requests' => $requests,
            'responses' => $responses
        ];

        $this->view->render($response, 'database.twig', $data);

        return $response;
    }
}