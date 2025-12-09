<?php
session_start();

// Simple endpoint to mark the current quote as dismissed/used
// This will prevent the job quote panel from being shown again
// for this session until a new job_id is provided.

$_SESSION['quote_dismissed'] = true;

http_response_code(204); // No Content
exit;
