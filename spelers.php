<html>
	<head>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
		<style>
			div.blueTable {
			  border: 1px solid #1C6EA4;
			  background-color: #EEEEEE;
			  width: 80%;
			  text-align: left;
			  border-collapse: collapse;
			}
			.divTable.blueTable .divTableCell, .divTable.blueTable .divTableHead {
			  border: 1px solid #AAAAAA;
			  padding: 3px 2px;
			}
			.divTable.blueTable .divTableBody .divTableCell {
			  font-size: 13px;
			}
			.divTable.blueTable .divTableRow:nth-child(even) {
			  background: #D0E4F5;
			}
			.divTable.blueTable .divTableHeading {
			  background: #1C6EA4;
			  background: -moz-linear-gradient(top, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
			  background: -webkit-linear-gradient(top, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
			  background: linear-gradient(to bottom, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
			  border-bottom: 2px solid #444444;
			}
			.divTable.blueTable .divTableHeading .divTableHead {
			  font-size: 15px;
			  font-weight: bold;
			  color: #FFFFFF;
			  border-left: 2px solid #D0E4F5;
			}
			.divTable.blueTable .divTableHeading .divTableHead:first-child {
			  border-left: none;
			}

			.blueTable .tableFootStyle {
			  font-size: 14px;
			  font-weight: bold;
			  color: #FFFFFF;
			  background: #D0E4F5;
			  background: -moz-linear-gradient(top, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
			  background: -webkit-linear-gradient(top, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
			  background: linear-gradient(to bottom, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
			  border-top: 2px solid #444444;
			}
			.blueTable .tableFootStyle {
			  font-size: 14px;
			}
			.blueTable .tableFootStyle .links {
				 text-align: right;
			}
			.blueTable .tableFootStyle .links a{
			  display: inline-block;
			  background: #1C6EA4;
			  color: #FFFFFF;
			  padding: 2px 8px;
			  border-radius: 5px;
			}
			.blueTable.outerTableFooter {
			  border-top: none;
			}
			.blueTable.outerTableFooter .tableFootStyle {
			  padding: 3px 5px;
			}
			/* DivTable.com */
			.divTable{ display: table; }
			.divTableRow { display: table-row; }
			.divTableHeading { display: table-header-group;}
			.divTableCell, .divTableHead { display: table-cell;}
			.divTableHeading { display: table-header-group;}
			.divTableFoot { display: table-footer-group;}
			.divTableBody { display: table-row-group;}

input[type=checkbox] {
    transform: scale(2);
    -ms-transform: scale(2);
    -webkit-transform: scale(2);
    padding: 10px;
}
		</style>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	</head>
	<body>

<?php

//error_reporting(-1);
//ini_set('display_errors', 'On');
$rCon = mysqli_connect("localhost", "u64368p146036_voetbal", "3plead-Underdog2-ludicrous", "u64368p146036_voetbal");

print "<H3><a href='http://jverhoeff.com/voetbal0101/voetbal001.php'>Invoeren</a></H3>";
print "<H3><a href='http://jverhoeff.com/voetbal0101/spelers.php'>Spelers</a></H3>";
print "<H3><a href='http://jverhoeff.com/voetbal0101/overzicht.php'>Overzicht</a></H3><br>";

$speler_id = $_POST['id'];
$speler_actief = $_POST['actief'];
$speler_naam = $_POST['naam'];
$speler_email = $_POST['email'];
$speler_type = $_POST['type'];


if (isset($_POST) && !empty($_POST)) {
	$tel_speler = 0;
	
	foreach ($speler_id as $a => $b) {
		if($speler_actief[$a] == "on") {$actief_value = '1';}
		else {$actief_value = '0';}
			
		//print $speler_naam[$a]." == ".$actief_value. " == ".$speler_actief[$a]."<br>";
		$update = "UPDATE spelers SET actief='" . $actief_value . "',naam='" . $speler_naam[$a] . "',email='" . $speler_email[$a] . "',type='" . $speler_type[$a] . "' WHERE id='" . $a . "'";
		$query_update = mysqli_query($rCon, $update) or die(mysqli_error());
		$tel_speler = $tel_speler+1;
	}
}

if ($_GET['action'] == "delete") {
	echo "<div class='alert alert-danger' role='alert'>";
	echo "Weet je het zeker?<br /><br />";
	echo "<H4><a href=spelers.php?delete=ja&spelerid=".$_GET['spelerid']."><span class='label label-danger'>Ja</span></a>";
			echo "  <a href=spelers.php><span class='label label-danger'>Nee</span></a></H4>";
	echo "</p></div>";

}

if ($_GET['delete'] == "ja") {
	$query_delete1 = "DELETE FROM spelers WHERE id =".$_GET['spelerid'];
	$query_delete = mysqli_query($rCon, $query_delete1) or die(mysqli_error());
	echo "<div class='alert alert-danger' role='alert'>";
	echo "<strong><center>Speler is verwijderd!</center></strong>";
	echo "</div>";
	echo "<head><meta http-equiv=\"refresh\" content=\"2;url=spelers.php\"></head>";
}


$k = 0;
$sQuery = 'SELECT * FROM spelers s ORDER BY naam';
$rResult = mysqli_query($rCon, $sQuery) or die(mysql_error());

print "<form method='post' action=''>";
print '<div class="divTable blueTable">
			<div class="divTableHeading">
			<div class="divTableRow">
			<div class="divTableHead">Actief</div>
			<div class="divTableHead">Naam</div>
			<div class="divTableHead">Email</div>
			<div class="divTableHead">Startscore</div>
			<div class="divTableHead">Huidige score</div>
			<div class="divTableHead">Type</div>
			<div class="divTableHead">Optie</div>
			</div>
			</div>
			<div class="divTableBody">';
echo "<div class='divTableRow'><input name ='updateform1' type='submit' value='Update!' /></div>";

while ($rRow = mysqli_fetch_assoc($rResult)) {
$speler_id = $rRow['id'];
	$query_scores = mysqli_fetch_assoc(mysqli_query($rCon, 'SELECT * FROM wedstrijd w WHERE speler_id = "'.$speler_id.'" ORDER BY id DESC LIMIT 0, 1'));
	echo '<div class="divTableRow"><input size="3" type="text" name="id[' . $rRow['id'] . ']" value="'. $rRow['id'].'" hidden>';
	//echo '<input type="checkbox"'; if($rRow['actief'] == '1'){ print " checked='checked' value='on'"; } echo ' name="player[' . $rRow['id'] . ']">';
	echo '<div class="divTableCell"><input type="checkbox"'; if($rRow['actief'] == True) { echo " checked='checked' "; } else { echo " ";} echo ' name="actief[' . $rRow['id'] . ']"></div>';
	echo '<div class="divTableCell"><input type="text" size="20" name="naam[' . $rRow['id'] . ']" value="'. $rRow['naam'].'"></div>';
	echo '<div class="divTableCell"><input type="email" size="30" name="email[' . $rRow['id'] . ']" value="'. $rRow['email'].'"></div>';
	echo '<div class="divTableCell"><input type="number" size="4" name="score[' . $rRow['id'] . ']" value="'. $rRow['score'].'" disabled></div>';
	echo '<div class="divTableCell"><input type="number" size="4" name="activescore[' . $rRow['id'] . ']" value="'. $query_scores['score'].'" disabled></div>';
	echo '<div class="divTableCell"><input type="text" name="type[' . $rRow['id'] . ']" size="2" value="'. $rRow['type'].'"></div>';
	echo '<div class="divTableCell"><a href=spelers.php?action=delete&spelerid='.$rRow['id'].'><img src="img/del.gif" border="0"></a></div></div>';
}
echo "<div class='divTableRow'><input name ='updateform1' type='submit' value='Update!' /></div>";
print "</form>";
print '</div></div>';
?>



	</body>
</html>