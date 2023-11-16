<?php
	if (isset($_GET['date'])) {
		$sKey = 'voetbal2013';
		$rCon = mysqli_connect("localhost", "u64368p146036_voetbal", "3plead-Underdog2-ludicrous", "u64368p146036_voetbal");
echo '<form method="post" action="">';	
		
	$sDatum = base64_decode($_GET['date']);

	if (count($_POST) > 0) {
		foreach ($_POST as $iSpeler => $fCijfer) {
			mysqli_query($rCon, 'INSERT INTO cijfer (datum, speler_id, cijfer) VALUES ("' . $sDatum . '", ' . $iSpeler . ',"' . str_replace(',', '.', $fCijfer) . '")');
		}

		unset($_POST);
		echo 'Bedankt.';
	} else {
		echo '<h1>Cijfers geven ' . $sDatum . '</h1>';

		$sQuery = 'SELECT * FROM uitslag WHERE datum = "' . $sDatum . '"';
		$rResult = mysqli_query($rCon, $sQuery);
		$rRow = mysqli_fetch_assoc($rResult);

		echo '<h2>Uitslag ' . $rRow['voor'] . '-' . $rRow['tegen'] . '</h2>';

		echo 'Je mag samen met 2 anderen de spelers van afgelopen vrijdag een cijfer van 5 tot 10,0 geven.<br/>
		5 is slecht, 10 is super goed natuurlijk :) <br/>
        De cijfers worden gebruikt om de wedstrijden nog leuker te maken!<br/><br/>';

		$sQuery = 'SELECT * FROM wedstrijd w, spelers s WHERE w.speler_id=s.id AND datum = "' . $sDatum . '" AND team = 1';
		$rResult = mysqli_query($rCon, $sQuery);

		echo '<h2>Team 1</h2>';
		while ($rRow = mysqli_fetch_assoc($rResult)) {
			if ($rRow['speler_id'] == $_GET['speler']) {
				echo $rRow['naam'] . '<br/>';
			} else {
				echo '<input type="text" name="' . $rRow['speler_id'] . '" style="width:25px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $rRow['naam'] . '<br/>';
			}
		}

		$sQuery = 'SELECT * FROM wedstrijd w, spelers s WHERE w.speler_id=s.id AND datum = "' . $sDatum . '" AND team = 2';
		$rResult = mysqli_query($rCon, $sQuery);

		echo '<h2>Team 2</h2>';
		while ($rRow = mysqli_fetch_assoc($rResult)) {
			if ($rRow['speler_id'] == $_GET['speler']) {
				echo $rRow['naam'] . '<br/>';
			} else {
				echo '<input type="text" name="' . $rRow['speler_id'] . '" style="width:25px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $rRow['naam'] . '<br/>';
			}
		}

		echo '<br/><br/><input type="submit" value="Sla ze op">';
	}
	echo "</form>";
}
else {
	echo "Nothing here!";
}