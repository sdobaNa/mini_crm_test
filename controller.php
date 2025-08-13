<?php

require "DbConnection.php";
require "service/DealService.php";
require "service/ContactService.php";

$connection = DbConnection::getConnection();

$dealService = new DealService($connection);
$contactService = new ContactService($connection);

$action = $_GET['action'] ?? null;
$type = $_GET['type'] ?? null;
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'save':
            $input = json_decode(file_get_contents('php://input'), true);
            if ($type === 'deals') {
                $dealService->save($input);
            } else if ($type === 'contacts') {
                $contactService->save($input);
            }
            break;

        case 'update':
            $input = json_decode(file_get_contents('php://input'), true);
            if ($type === 'deals') {
                $dealService->update($input);
            } else if ($type === 'contacts') {
                $contactService->update($input);
            }
            break;

        case 'delete':
            if (!$id || !$type) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing id or type']);
                exit;
            }

            if ($type === 'deals') {
                $dealService->delete($id);
            } elseif ($type === 'contacts') {
                $contactService->delete($id);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => "Unknown action $action"]);
            break;

    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    switch ($action) {
        case 'list':
            if ($type === 'deals') {
                echo json_encode($dealService->getAll());
            } elseif ($type === 'contacts') {
                echo json_encode($contactService->getAll());
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Unknown type']);
            }
            break;

        case 'item':
            if (!$id || !$type) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing id or type']);
                exit;
            }

            if ($type === 'deals') {
                echo json_encode($dealService->getWithContracts($id));
            } elseif ($type === 'contacts') {
                echo json_encode($contactService->getWithDeals($id));
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Unknown type']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => "Unknown action $action"]);
            break;
    }
}
