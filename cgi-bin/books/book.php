<?php

if (!isset($_SESSION)) {
    session_start();
}
;
ob_start();
$_SESSION['url'] = $_SERVER['REQUEST_URI'];

include '../connectToDB.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
        <title id='pageTitle'>Adam's Sandbox</title>";
include('../header.php');
echo "</head><body><div class='container'>";
include('../navigation.php');
if (isset($_SESSION['userid'])) {
    $user_id = $_SESSION['userid'];
}
$username = 'Your';
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'] . '\'s';
}
if (isset($_SESSION['usergroup'])) {
    if ($_SESSION['usergroup'] == 'User' or $_SESSION['usergroup'] == 'Admin') {
        
        $start_rank = 1;
        
        if (isset($_GET['rank'])) {
            $start_rank = $_GET['rank'];
        }
        
        if (isset($_POST['rank'])) {
            $start_rank = $_POST['rank'];
        }
        
        $booksql        = "SELECT * FROM orion.books m, orion.g_user_books g WHERE (rank >= $start_rank or rank = 0 ) and m.id = g.books_id and g.completed = 1 and g.user_id =" . $user_id . " order by rank";
        $bookquery      = $db->query($booksql);
        $sqlgamesum     = "SELECT count(*) as Count FROM orion.books m, orion.g_user_books g WHERE g.completed = 1 and m.id = g.books_id  and g.user_id =" . $user_id;
        $querygamesum   = $db->query($sqlgamesum);
        $resultsgamesum = $querygamesum->fetch(PDO::FETCH_ASSOC);
        
        echo "<div class='col-md-12'><a href='book.php?rank=" . $start_rank . "' class='fixed_middle_right' ><span class='glyphicon glyphicon-refresh'></span></a></div>
      <div class='col-md-3'></div>
            <div class='col-md-6'>
                    <div class='text-center'><h1>" . $username . " Books</h1>
          <a href='readlist.php' class='btn btn-lg btn-inverse btn-block' ><span class='glyphicon glyphicon-eye-open'></span> Readlist</a>
          <a href='findbook.php' class='btn btn-lg btn-inverse btn-block' ><span class='glyphicon glyphicon-plus'></span> Add a Book</a>
                    <h3>Books Read:" . $resultsgamesum['Count'] . "</h3>
          <form class='form-signin' action='book.php' form='thisForm' method='POST'>
          <div class='input-group'>
            <input type='hidden' id='table' value='books'>
            <input type='number' class='form-control' id='rank' name='rank' value='" . $start_rank . "'>
            <span class='input-group-btn'>
              <button class='btn btn-default' type='submit'>Go To...</button>
            </span>
          </div>
          </form></br>
                    <ul class='list-group' id='list-items'>";
        
        foreach ($bookquery as $item) {
            echo "<li draggable=true class='list-group-item' id='item_" . ($item['g_id'] . "'><a href='bookdetails.php?id=" . $item['books_id'] . "'><img src='" . $item['poster_url'] . "' class='img-rounded img-responsive' style='width:30px;height:20px;float:left'><span class='badge'>" . $item['rank'] . "</span>   " . $item['title'] . "</a></li>");
        }
        echo "    </ul>
        </div>";
        
    } else
        header("location: ../users/signin.php");
} else
    header("location: ../users/signin.php");
include('../footer.php');
echo "</div></body></html>";
?>