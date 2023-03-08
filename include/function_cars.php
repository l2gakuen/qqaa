<?php

function compare_dates($date1, $date2)
{
	$date1 = new DateTime($date1);
	$date2 = new DateTime($date2);
	$interval = $date1->diff($date2);
	return $interval->days;
}
function display_status($int, $colors = false)
{
	switch ($int) {
		case 0:
			$string = $colors ? '' : "Désactivé";
			break;
		case 1:
			$string = $colors ? 'one' : "Disponible";
			break;
		case 2:
			$string = $colors ? 'two' : "En Location";
			break;
		case 3:
			$string = $colors ? 'three' : "En Réparation";
			break;
	}
	return $string;
}
function display_energy($int, $colors = false, $dump = false)
{
	$energies = (array) ['Electrique', 'Hybride', 'Diesel', 'Essence'];
	switch ($int) {
		case 0:
			$string = $colors ? 'one' : $energies[0];
			break;
		case 1:
			$string = $colors ? 'two' : $energies[1];
			break;
		case 2:
			$string = $colors ? 'three' : $energies[2];
			break;
		case 3:
			$string = $colors ? 'four' : $energies[3];
			break;
	}
	return $dump ? $energies : $string;
}

function pdftk($pdf_file, $fdf_file, $settings)
{
	//------------------------------------------

	$descriptorspec = array(
		0 => array("pipe", "r"),  // // stdin 
		1 => array("pipe", "w"),  // stdout 
		2 => array("pipe", "w") // stderr 
	);

	$output_modes = $settings['output_modes'];
	$security = $settings['security'];

	$cwd = '/tmp';
	$env = array('misc_options' => 'aeiou');
	$err = '';
	$success = 0;

	if (is_windows()) {
		$cmd = "pdftk.exe"; //For windows
	} else {
		$cmd = "pdftk"; //For linux and mac
	}

	$dircmd = fix_path(dirname(__file__));

	if (file_exists("$dircmd/$cmd")) {

		$pdf_out = FPDM_CACHE . "pdf_flatten.pdf";

		$cmdline = "$dircmd/$cmd \"$pdf_file\" fill_form \"$fdf_file\" output \"$pdf_out\" $output_modes $security"; //direct to ouptut	

		//echo htmlentities("$cmdline , $descriptorspec, $cwd, $env");

		if (PHP5_ENGINE) { // Php5
			$process = proc_open($cmdline, $descriptorspec, $pipes, $cwd, $env);
		} else { //Php4
			$process = proc_open($cmdline, $descriptorspec, $pipes);
		}

		if (is_resource($process)) {

			if (PHP5_ENGINE) {
				$err = stream_get_contents($pipes[2]);
			} else { //Php4
				$err = "";
				while (($str = fgets($pipes[2], 4096))) {
					$err .= "$str\n";
				}
			}

			fclose($pipes[2]);

			//Its important to close the pipes before proc_close call to avoid  dead locks 
			$return_value = proc_close($process);
		} else {
			$err = "No more resource to execute the command";
		}
	} else {
		$err = "Sorry but pdftk binary is not provided / Cette fonctionnalite requiere pdftk non fourni ici<ol>";
		$err .= "<li>download it from / telecharger ce dernier a partir de <br><blockquote><a href=\"http://www.pdflabs.com/docs/install-pdftk/\">pdflabs</a></blockquote>";
		$err .= "<li>copy the executable in this directory / Copier l'executable dans<br><blockquote><b>$dircmd</b></blockquote>";
		$err .= "<li>set \$cmd to match binary name in / configurer \$cmd pour  qu'il corresponde dans le fichier<br><blockquote><b>" . __file__ . "</b></blockquote></ol>";
	}

	if ($err) {
		$ret = array("success" => false, "return" => $err);
	} else
		$ret = array("success" => true, "return" => $pdf_out);

	return $ret;
}

//Expert patching xD
function fix_data($data)
{
	if (!is_array($data)) {
		return $data;
	}

	$fixed_data = array();
	foreach ($data as $key => $value) {
		$fixed_data[$key] = isset($value) ? $value : '_';
	}

	return $fixed_data;
}
