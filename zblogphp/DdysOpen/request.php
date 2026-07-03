<?php
require '../../../zb_system/function/c_system_base.php';
$zbp->Load();
require_once dirname(__FILE__) . '/function.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    DdysOpen_RequestResponse(DdysOpen_Error('Method not allowed.', 405, array()));
}

$result = DdysOpen_HandleRequestForm();
DdysOpen_RequestResponse($result);

function DdysOpen_RequestResponse($result)
{
    $isJson = isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    if ($isJson) {
        header('Content-Type: application/json; charset=utf-8');
        if (DdysOpen_IsError($result)) {
            http_response_code($result['status'] ? $result['status'] : 400);
            echo json_encode(array('success' => false, 'message' => $result['message']));
        } else {
            echo json_encode(array('success' => true, 'message' => 'Request submitted.', 'data' => DdysOpen_PayloadData($result)));
        }
        die();
    }

    $redirect = DdysOpen_Post('redirect', '');
    if ($redirect === '') {
        $redirect = '../../../';
    }
    $status = DdysOpen_IsError($result) ? 'failed' : 'ok';
    $join = strpos($redirect, '?') === false ? '?' : '&';
    header('Location: ' . $redirect . $join . 'ddys_request_status=' . $status);
    die();
}
