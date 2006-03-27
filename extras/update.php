<?php
	/* $Id: update.php,v 1.1 2001/01/16 00:10:11 pkellum Exp $ */
	include '../mysql.php';
	// if a readme.txt file exists, display it to the user
	if(!$read_me) {
		if(file_exists('readme.txt')) {
			$readme_file = 'readme.txt';
		}
		elseif(file_exists('README')) {
			$readme_file = 'README';
		}
		elseif(file_exists('readme')) {
			$readme_file = 'readme';
		}
		if($readme_file) {
			$readme = file($readme_file);
			print "<CENTER><TABLE BORDER=\"1\" WIDTH=\"75%\" CELLPADDING=\"2\" CELLSPACING=\"0\"><TR BGCOLOR=\"#e7e7cc\"><TD>\n";
			print nl2br(htmlentities(implode($readme, ' ')));
			print "<HR NOSHADE SIZE=\"1\"><CENTER><A HREF=\"update.php?read_me=1\"><B>Continue</B></A></CENTER>\n";
			print "</TD></TR></TABLE>\n";
			exit;
		}
	}
	// list all sql files in this directory for the user to choose from
	if(!$filename) {
		print "<B>Select an SQL file to install.</B><UL>\n";
		$files = dir('.');
		while($f = $files->read()) {
			if(substr($f, -4, 4) == '.sql') {
				print "<LI><A HREF=\"update.php?read_me=1&filename=$f\">$f</A>";
				$sql_file = file($f);
				if(substr($sql_file[0], 0, 8) == '# Brief:')  {
					print " - " . htmlentities(substr($sql_file[0], 9));
				}
				print "</LI>\n";
			}
		}
		print "</UL>";
	}
	// look like we've decided on a file
	else {
		// read in the sql file and parse it into a form
		if(!$action) {
			$sql_file = file($filename);
			$sql_statements = array();
			// create a new array containing just the lines we want.
			foreach($sql_file as $k=>$v) {
				// get rid of whitespace
				$sql = trim($v);
				// no comments, please
				if(substr($sql, 0, 1) == '#') {
					continue;
				}
				// no blank lines
				if(!$sql) {
					continue;
				}
				// insert the current sql line into a buffer
				$cur_sql .= $sql . ' ';
				// get the ending character.  if it's a ';' then we have a full statement, otherwise keep appending until we do.
				if(substr($sql, -1, 1) == ';') {
					$sql_statements[] = substr(trim($cur_sql), 0, -1);
					$cur_sql = '';
				}
			}
			// ok, we got our new array 'sql_statements' containing a full sql statement per element
			// now, let's display them in a form to allow modification before commiting
			print "<B>Check over the following SQL statements and make sure they are correct for your server.  If so, click the &quot;Commit&quot; button.</B><BR><BR>\n";
			print "<FORM METHOD=\"post\" ACTION=\"update.php\">\n";
			print "<INPUT TYPE=\"hidden\" NAME=\"action\" VALUE=\"commit\">\n";
			print "<INPUT TYPE=\"hidden\" NAME=\"filename\" VALUE=\"$filename\">\n";
			print "<INPUT TYPE=\"hidden\" NAME=\"read_me\" VALUE=\"1\">\n";
			foreach($sql_statements as $k=>$v) {
				print "<B>" . $k . ":</B> <TEXTAREA NAME=\"sql_statements[$k]\" ROWS=\"3\" COLS=\"40\" WRAP=\"soft\">" . htmlentities($v) . "</TEXTAREA><BR><BR>\n";
			}
			print "<INPUT TYPE=\"submit\" VALUE=\"Commit\">";
			print "</FORM>";
		}
		// commit the changes to the database
		else {
			foreach($sql_statements as $k=>$v) {
				$result = mysql_query(stripslashes($v));
				if(!$result) {
					print "<B>Error:</B> the following SQL statement didn't work.<BR>\n";
					print mysql_error() . '<BR><BR>';
					print "<BLOCKQUOTE>\n" . htmlentities(stripslashes($v)) . "\n</BLOCKQUOTE>";
					print "<HR NOSHADE SIZE=\"1\">";
				}
			}
			print "<H1 ALIGN=\"center\">SQL Database Updated!</H1>";
		}
	}
?>