<?php
session_start(); // Connect to the existing session
processPageRequest();
function displaySearchResults($searchString){
	$apiKey = "89270bfb";
	if(isset($_POST["search"])){
		$searchString = $_POST["searchString"];
	$results = file_get_contents('http://www.omdbapi.com/?apikey=89270bfb&s='.urlencode($searchString).'&type=movie&r=json');
	$array = json_decode($results, true)["Search"];
	$count = count($array);
	if($count > 0){
	echo '
	<!DOCTYPE html>
	<html>
	<head>
		<title>Searching - myXpress Movies!</title>
		<script src="script.js"></script>
	</head>
	<body>
	<h1>myMovies Xpress!</h1>
	<div style="text-align: left">
		<p>Welcome, '.$_SESSION["name"].' (<a href="#" onclick="confirmLogout()">logout</a>)</p>
	</div>
	<p>' .$count . ' Movies Found</p>';
	echo '
		<table border="3" style="padding: 15px;">
		<thead>
		<tr>
		<th>Movie Image</th>
		<th>Movie title</th>
		<th>Add Item</th>
		</tr>
		</thead>
		<tbody>';
	$x = 0;
	while($x < $count){
			$movieID = trim($array[$x]["imdbID"]);
			$img = $array[$x]["Poster"];
			$year = $array[$x]["Year"];
			$title = $array[$x]["Title"];

			$url = "https://www.imdb.com/title/$movieID/";
			echo '<tr>';
			echo '<td><img src="'.$img.'" height="100px" /></td>';
			echo '<td><h3><a href="'.$url.'" targer="_blank">'.$title.' : '.$year.'</a></h3></td>';
			echo "<td><a href='#' onclick='addMovie(\"".$movieID."\")'>+</a></td>";
			echo'</tr>';
			
			$x++; 
		}
		$redirect = "window.location.href='./index.php'";
		echo '
			</tbody>
			</table>
			<br>
			<button onclick="confirmCancel\'search\'">Cancel</button><br/><br/>
			</body>
		</html>';
	}
}
}
	
function displaySearchForm(){
	$redirect = "location='./index.php'";
	echo '
    <!DOCTYPE html>
    <html>
    <head>
    	<title>Mainpage</title>
    	<script src="script.js"></script>
    </head>
    <body>
			</style>
    	<h1>myMovies Xpress!</h1>
    	<div style="text-align: left" class="">
		    <p>Welcome, '.$_SESSION["name"].' <a href="#" onclick="confirmLogout()">Logout</a></p>
    	</div>
			<form action="./search.php" method="post">
				<p><label>Search:</label>
				<input type="text" name="searchString" required/></p>
				<p><a href="#" onclick="confirmCancel(\'search\')">Home</a></p>
				<p><button onclick="'.$redirect.'">Cancel</button></p>
				<p><input type="reset" name="reset" value="Clear"></p>
				<p><input type="submit" name="search" value="Search"></p>
			</form>
		</body>
	</html>
';
}
function processPageRequest(){
	if(!isset($_SESSION['name'])){
		header('logon.php');
		exit();
	}
	if(!isset($_POST["searchString"])){
		displaySearchForm();
	} else{
		displaySearchResults($_POST["searchString"]);
	}
}
?>
