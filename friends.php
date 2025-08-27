<?php
session_start();
 
// If not logged in, redirect to login
if(!isset($_SESSION['user_id'])){
    echo "<script>window.location.href='login.php';</script>";
    exit();
}
 
// Database connection
$host = "localhost";
$user = "upknjbhg8vsv8";
$pass = "yz88ljtio3sf";
$dbname = "dbpsrrjhmvsjmz";
 
$conn = new mysqli($host, $user, $pass, $dbname);
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
 
$currentUser = $_SESSION['user_id'];
 
// Fetch all users except current one
$sql = "SELECT id, username FROM users WHERE id != '$currentUser'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>WhatsApp Clone - Friends</title>
<style>
body {
  margin: 0;
  font-family: Arial, sans-serif;
  display: flex;
  height:
 
