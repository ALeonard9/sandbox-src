<?php

session_start();
ob_start();
$_SESSION['url'] = $_SERVER['REQUEST_URI'];

include '../connectToDB.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
        <title id='pageTitle'>LeoNine Studios</title>";
include('../header.php');
echo "</head><body><div class='container'>";
include('../navigation.php');
$user_id = $_SESSION['userid'];

if ($_SESSION['usergroup'] == 'User' or $_SESSION['usergroup'] == 'Admin'){

if (isset($_GET['id'])) {
  $show_id = $_GET['id'];
}
$metricssql = "SELECT count(*) as subset, (SELECT COUNT(*) FROM orion.g_user_tvepisodes g, orion.tvepisodes e WHERE g.tvepisode_id = e.id AND tv_id = ".$show_id." AND user_id = ".$user_id." AND e.airdate <= '".date('Y-m-d')."') AS total FROM orion.g_user_tvepisodes g, orion.tvepisodes e WHERE g.tvepisode_id = e.id AND tv_id = ".$show_id." AND user_id = ".$user_id." AND watched = 1";
$metricquery = $db->query($metricssql);
         $metrics = $metricquery->fetch(PDO::FETCH_ASSOC);
$titlesql = "SELECT title as title FROM orion.tv where id = $show_id";
$querytitle = $db->query($titlesql);
         $series_title = $querytitle->fetch(PDO::FETCH_ASSOC);
$sql = "SELECT g.g_id, e.title, e.season, e.season_number, g.watched FROM orion.g_user_tvepisodes g, orion.tvepisodes e WHERE g.tvepisode_id = e.id AND e.tv_id = ".$show_id." AND user_id = ".$user_id." AND e.airdate <= '".date('Y-m-d')."' order by e.season ASC, e.season_number ASC";
$query = $db->query($sql);
echo "<div class='col-md-3'></div>
			<div class='col-md-6'><h1 class='text-center'>".$series_title['title']."</h1>
      <h3 class='text-center'><span id='done'>".$metrics['subset']."</span>/<span id='total'>".$metrics['total']."</span> : <span id='percent'></span>%</h3>";
if ($metrics['total'] - $metrics['subset'] > 0 ) {
  echo "<a class='btn btn-lg btn-inverse btn-block fullseason' >Watched All</a><br/>";
}
echo "<div class='panel-group'>";
        $season = 0;
        foreach($query as $item){
          if($season != $item['season'] && $season != 0){
            echo "</ul></div></div>";
          }
          if($season != $item['season']){
            $season = $item['season'];
            echo "<div class='panel panel-default'>
            <div class='panel-heading'>
               <h4 class='panel-title'>
                 <a data-toggle='collapse' href='#collapse".$item['season']."'>Season ".$item['season']."</a>
               </h4>
             </div>
             <div id='collapse".$item['season']."' class='panel-collapse collapse in'>
               <ul class='list-group'>";
          }
          $classw = 'unwatched';
          $displayw = 'Not Watched';
          if($item['watched'] == 1) {
            $classw = 'watched';
            $displayw = 'Watched';
          }
          echo "<li class='list-group-item'>".$item['season_number'].". ".$item['title']."<button class='pull-right ".$classw."' type='button' id='".$item['g_id']."'>".$displayw."</button></li>";
        }
        echo "</div></div>";
}
else
	  header("location: findtv.php");

include('../footer.php');
echo "</div>
<script type='text/javascript'>
$(document).ready(function () {
  var watched = 0;
  $('.watched,.unwatched').on('click', function () {
    $(this).toggleClass('watched').toggleClass( 'unwatched' );
    if ($(this).html() == 'Watched') {
      $(this).html('Not Watched');
      watched = 0;
      changeDone(-1);
    } else {
      $(this).html('Watched');
      watched = 1;
      changeDone(1);
    }
    $.ajax({
     type: 'POST',
     url: 'watchpivot.php?id=' + $(this).attr('id') + '&watched=' + watched
    }).done(function( msg ) {
    });
    updateMetrics();
  });
  $('.fullseason').on('click', function () {
    $.ajax({
     type: 'POST',
     url: 'watchall.php?id=$show_id&watched=1&user_id=$user_id'
    }).done(function( msg ) {
    });
    location.reload();
  });
  $('.panel-collapse').each( function( index, el ) {
    if($(el).children().children().children('.unwatched').length == 0) {
     $(el).removeClass('in');
    }
  });

  function updateMetrics() {
    var done = parseInt($('#done').text());
    var total = parseInt($('#total').text());
    var final = ((done/total)* 100).toFixed(2);
    $('#percent').html(final);
  }
  function changeDone(add) {
    var done = parseInt($('#done').text());
    var final = done + add;
    $('#done').html(final);
  }
  updateMetrics();
});
</script>
</body></html>";
?>
