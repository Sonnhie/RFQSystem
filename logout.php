<?php 
    session_start();
    session_unset();
    session_destroy();
    
    $response = [
        'status' => 'success',
        'message' => 'You have been logged out successfully.',
    ];
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit(); 
?>