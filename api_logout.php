<?php
require_once 'db.php';
session_destroy();
json_response(['ok'=>true]);
