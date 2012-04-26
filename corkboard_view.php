<?php
session_start();
if(!session_is_registered(myusername)){
header("location:login/mainlogin.php");
}
$host="academic-mysql.cc.gatech.edu"; // Host name 
$username="cs4400_group29"; // Mysql username 
$password="56wVseal"; // Mysql password 
$db_name="cs4400_group29"; // Database name 
$tbl_name="User"; // Table name

// Connect to server and select databse.
mysql_connect("$host", "$username", "$password")or die("cannot connect"); 
mysql_select_db("$db_name")or die("cannot select DB");

$cbquery = sprintf("
	SELECT c.Email, u.UserName, c.CatName, c.LastUpdate
        FROM Corkboard c
        LEFT JOIN User AS u ON u.Email = '%s'
        WHERE c.Title = '%s'
        LIMIT 1
	",
	mysql_real_escape_string($_GET['email']),
	mysql_real_escape_string($_GET['title'])
);

$result = mysql_query($cbquery);
$row = mysql_fetch_assoc($result);
$cbuser = $row['UserName'];
$cbcat = $row['CatName'];
$cbowner = $row['Email'];

$followquery = sprintf("
SELECT * FROM Follow
WHERE Follower = '%s'
AND Followee = '%s'",
mysql_real_escape_string($_SESSION['myusername']),
mysql_real_escape_string($cbowner));
$followresult = mysql_query($followquery);
$following = 0;
if (mysql_num_rows($followresult) != 0) {
	$following = 1;
}

$watchquery = sprintf("
SELECT * FROM Watch
WHERE User = '%s'
AND CorkboardTitle = '%s'
AND CorkboardOwner = '%s'",
mysql_real_escape_string($_SESSION['myusername']),
mysql_real_escape_string($_GET['title']),
mysql_real_escape_string($cbowner));
$watchresult = mysql_query($watchquery);
$watching = 0;
if (mysql_num_rows($watchresult) != 0) {
	$watching = 1;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<LINK REL=StyleSheet HREF="layout.css" TYPE="text/css" MEDIA=screen>
		<title><?php echo $_GET["title"]?></title>
	</head>
	<body>
		<img src="images/logo_small.png" alt="logo" />
		<br><br>
		
		
		<table align="Left">
			<tr>
				<td><h2><?php echo $_GET["title"]?></h2></td>
				<td>
				<?php		
				if ($_SESSION['myusername'] != $cbowner && $watching == 0) {	
					echo "<td><form method='post' action='watch.php'>";
					echo    "<input type='hidden' name='back' value=".$_SERVER['REQUEST_URI'].">";
					echo	"<input type='hidden' name='email' value=".$cbowner.">";
					echo	"<input type='hidden' name='title' value=".$_GET['title'].">";				
					echo	"<input type='submit' value='Watch'>";
					echo "</form></td>";
				}
				?>
				</td>
			</tr>
			<tr>
				<td>Owner: <?php echo $cbuser ?></td>
				<td>
				<?php		
				if ($_SESSION['myusername'] != $cbowner && $following == 0) {	
					echo "<td><form method='post' action='follow.php'>";
					echo    "<input type='hidden' name='back' value=".$_SERVER['REQUEST_URI'].">";
					echo	"<input type='hidden' name='email' value=".$cbowner.">";			
					echo	"<input type='submit' value='Follow'>";
					echo "</form></td>";
				}
				?>
				</td>
			</tr>
			<tr>
				<td>Category:</td>
				<td><?php echo $cbcat ?></td>
			</tr>
			<tr>
				<td>This board has <?php 
					$watcherquery = sprintf("
					SELECT COUNT(User) 
					FROM Watch
					WHERE CorkboardTitle = '%s'
					AND CorkboardOwner = '%s'",
					mysql_real_escape_string($_GET['title']),
					mysql_real_escape_string($_GET['email']));
					$result = mysql_query($watcherquery);
					$row = mysql_fetch_assoc($result);
					echo $row['COUNT(User)']." ";
				?>watchers</td>
			</trl>
		<tr>
		
		<td><h3>Pushpins</h3></td>
		<?php		
		if ($_SESSION['myusername'] == $cbowner) {	
			echo "<td><form method='post' action='pushpin_add.php'>";
			echo	"<input type='hidden' name='email' value=".$cbowner.">";
			echo	"<input type='hidden' name='title' value=".$_GET['title'].">";				
			echo	"<input type='submit' value='Add Pushpin'>";
			echo "</form></td>";
		}
		?>
		</tr>
		<?php
			$cbtnquery = sprintf("
				SELECT DISTINCT Link
       				FROM PushPin
       				WHERE Email = '%s'
				AND CorkboardTitle = '%s'	
				",
				mysql_real_escape_string($_GET['email']),
				mysql_real_escape_string($_GET['title'])
			);
			$result = mysql_query($cbtnquery);
			while ($row = mysql_fetch_assoc($result)) {
				$link = $row['Link'];
				$email = $_GET['email'];
				$cbtitle = $_GET['title'];
				echo "<td>
					<a href='pushpin_view.php?email=$email&title=$cbtitle&link=$link'>
						<img class='thumbnail' src='$link'/>
					</a>
				</td>";
			}
		?>			
		</table>
		
		
		
		
    
	</body>
</html>
