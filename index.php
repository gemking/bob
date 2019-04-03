<?php
ini_set('display_errors', '1');
session_start(); // Connect to the existing session
require_once '/home/common/mail.php'; // Add email functionality
require_once'/home/common/dbInterface.php'; // Add database functionality
processPageRequest(); // Call the processPageRequest() function

function addMovieToCart($movieID){
    $movieId =  movieExistsInDB($movieID);
    if($movieId === 0){
    	$movie = file_get_contents('http://www.omdbapi.com/?apikey=89270bfb&i='.$movieID.'&type=movie&r=json');
    	$array = json_decode($movie, true);
	print_r($array);
    	$id = addMovie($array['imdbID'], $array['Title'], $array['Year'], $array['imdbRating'], $array['Runtime'], $array['Genre'], $array['Actors'], 
        $array['Director'], $array['Writer'], $array['Plot'], $array['Poster']);
    }

    addMovieToShoppingCart($_SESSION["ID"], $movieID);
    displayCart();	
}

function encoder(){ 
	$filename = "./data/cart.db";
	$encode = "";
	$rowList;
	$rowList = array();
	
	 $file = fopen( $filename, "r" );

     
    $filesize = filesize( $filename ); 
	if($filesize !== 0){
		while(!feof($file))
		  {
			$line = fgets($file);
			$data = explode(",", $line);
			foreach ($data as $text) {
				array_push($rowList, $text);
			}
		  }
	} 
    fclose($file);  
	
    $encode = $encode . '
    <!DOCTYPE html>
    <html>
    <head>
    	<title>Mainpage</title>
    	<script src="script.js"></script>
    </head>
    <body>
    <style>
		body{
			position: relative;
			max-width:1100px;
			margin: 0 auto;
		}
	</style>
    <h1>myMovies Xpress!</h1> 
';
	 
	if(count($rowList) > 0 && filesize($filename) && !empty($rowList)){ 
		$apiKey = "89270bfb";
		$encode = $encode . '
			<table border="1">
			<thead>
			<tr>
			<th>Movie Image</th>
			<th>Movie title</th>
			</tr>
			</thead>
			<tbody>'; 
		for ($x = 0; $x < count($rowList); $x++) { 
			$movie_id = trim($rowList[$x]);
			$movie = file_get_contents("http://www.omdbapi.com/?i=$movie_id&apikey=$apiKey&type=movie&r=json");
			$array = json_decode($movie, true); 
			$title = $array["Title"]; 
			$image = $array["Poster"]; 
			$year = $array["Year"]; 
			$url = "https://www.imdb.com/title/$movie_id/";
			$encode = $encode .  '<tr>'; 
			$encode = $encode .  '<td><img src="'.$image.'" height="100px" /></td>';
			$encode = $encode . '<td><h3><a href="'.$url.'" targer="_blank">'.$title.' : '.$year.'</a></h3></td>';
			$encode = $encode . '</tr>';
		}
		$redirect = "window.location.href='./search.php'";
		$encode = $encode .  '
			</tbody>
			</table> 
    </body>
    </html>
    ';  
    return $encode;
	}  
}




function checkout($name, $address){  
	$subject = "Your Receipt from myMovies!";
	$message = encoder();
	$address = "n01052448@ospreys.unf.edu"; 
	$decode = urldecode($message);
	$name = "bobthebuilder"; 
	$message = "
	<h3>Your transaction was successful.</h3>
	<p>You have successfully purchased the items below from our store</p>
	<p>$decode</p>
	<p>If u have any inquiries regarding your transaction, please contact our customer service support</p>";
	$result = sendMail(555409699, $address, $name, $subject, $message); 
	return $result;
}

function createMovieList($forEmail = false){
	$movieIds = isset($_SESSION['order']) ? getMoviesInCart($_SESSION['userId'], $_SESSION['order']) : getMoviesInCart($_SESSION['userId']);
	$string = '<table border="3" style="padding: 15px;">
	<thead>
	<tr>
	<th>Movie Image</th>
	<th>Movie title</th>
	<th>Remove Item</th>
	</tr>
	</thead>
	<tbody>';
	for ($x = 0; $x < count($movieIds); $x++) {
		$movie = getMovieData($movieIds[x]);
		$title = str_replace("'", " ", $array["Title"]);
		$image = $array["Poster"];
		$year = $array["Year"];
		$string .= '<tr>';
		$string .= '<td><img src="'.$image.'" height="100px" /></td>';
		$string .= '<td><h3>'.$title.' ('.$year.')</h3></td>';
		$string .= $forEmail ? '' : '<a href="javascript:void(0);" onclick=\'displayMovieInformation([movie_id]);\'>View More Info</a>';
		$string .= $forEmail ? '' : "<td><a href='#' onclick='confirmRemove(\"".$title."\", \"".$movie['ID']."\")'>X</a></td>";
		$string .= '</tr>';
	}
	$string .='</tbody></table>';
	return $string;
}

function displayCart($forEmail = false){
	$movieCount = countMoviesInCart($_SESSION["userId"]);
    echo '
    <!DOCTYPE html>
    <html>
    <head>
    	<title>Home - myXpress Movies!</title>
    	<script src="script.js"></script>
    </head>
    <body>
    <h1>myMovies Xpress!</h1>
    <div style="text-align: left" class="">
    	<p>Welcome, '.$_SESSION["displayName"].' </p>
    	<p><a href="#" onclick="confirmLogout()">Logout</a></p> 
    </div>
';
	if($movieCount > 0){
		$apiKey = "89270bfb";
		echo '<p>' . $movieCount . ' Movies in your shopping cart</p>
		<select id=\'select_order\' onchange=\'changeMovieDisplay();\'>
			<option value="0">Movie Title</option>
			<option value="1">Runtime (shortest -­> longest)</option>
			<option value="2">Runtime (longest ­-> shortest)</option>
			<option value="3">Year (old -­> new)</option>
			<option value="4">Year (new -­> old)</option>
		</select>
			<div id=\'shopping_cart\'>'. createMovieList($forEmail) . '</div>'; 
		
		$redirect = "window.location.href='./search.php'";
		echo '
			<br>
			' . ($forEmail ? '' : '<button onclick="'.$redirect.'">Add Movie</button><br><br>
			<button onclick="confirmCheckout()">Checkout</button>');
	} else {
		$redirect = "window.location.href='./search.php'";
		echo'<p>Add Some Movies to Your Cart</p>
		<br/>
		<button onclick="'.$redirect.'">Add Movie</button><br/><br/>';
	}
	echo ($forEmail ? '' : '<div id=\'modalWindow\' class=\'modal\'><div id=\'modalWindowContent\' class=\'modal-content\'></div></div>') . '</body></html>';
} 


function processPageRequest(){
	if(!isset($_SESSION['displayName'])){
		header('logon.php');
		
	}
	if(isset($_GET["action"])){
		switch($_GET["action"]){
			case "add":
				$movieID = $_GET["movie_id"];
				addMovieToCart($movieID);
				break;
			case "checkout":
				checkout($_SESSION["name"], $_SESSION["emailAddress"]);
				break;
			case 'remove':
				$movieID = $_GET["movie_id"];
				removeMovieFromCart($movieID);
				break;
			case 'update':
				$order = $_GET['order'];
				updateMovieListing($order);
				break;
			default:
				break;
		}
	} else {
		displayCart();
	}
}



function removeMovieFromCart($movieID){
	removeMovieFromShoppingCart($_SESSION['userId'], $movieID);
	displayCart();
}

function updateMovieListing($order){
	$_SESSION['order'] = $order;
	echo createMovieList(true);
}
