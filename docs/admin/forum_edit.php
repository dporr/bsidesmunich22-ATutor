<?php
/************************************************************************/
/* ATutor																*/
/************************************************************************/
/* Copyright (c) 2002-2005 by Greg Gay, Joel Kronenberg & Heidi Hazelton*/
/* Adaptive Technology Resource Centre / University of Toronto			*/
/* http://atutor.ca														*/
/*																		*/
/* This program is free software. You can redistribute it and/or		*/
/* modify it under the terms of the GNU General Public License			*/
/* as published by the Free Software Foundation.						*/
/************************************************************************/

$page = 'courses';
$_user_location = 'admin';

define('AT_INCLUDE_PATH', '../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');

if ($_SESSION['course_id'] > -1) { exit; }

require(AT_INCLUDE_PATH.'classes/Message/Message.class.php');
require(AT_INCLUDE_PATH.'lib/forums.inc.php');

global $savant;
$msg =& new Message($savant);

if (isset($_POST['cancel'])) {
	$msg->addFeedback('CANCELLED');
	header('Location: '.$_base_href.'admin/forums.php');
	exit;
} else if (isset($_POST['edit_forum'])) {
	//update forum
	$_POST['title']  = $addslashes($_POST['title']);
	$_POST['description']  = $addslashes($_POST['description']);
	$sql	= "UPDATE ".TABLE_PREFIX."forums SET title='" . $_POST['title'] . "', description='" . $_POST['description'] . "' WHERE forum_id=".$_POST['forum'];
	$result	= mysql_query($sql, $db);

	//remove the courses no longer using the forum
	$sql	= "SELECT course_id FROM ".TABLE_PREFIX."forums_courses WHERE forum_id=".$_POST['forum'];
	$result = mysql_query($sql,$db);
 	while ($row = mysql_fetch_assoc($result)) {
		if(!in_array($row['course_id'], $_POST['courses'])) {
			//delete
			$sql2	= "DELETE FROM ".TABLE_PREFIX."forums_courses WHERE forum_id=" . $_POST['forum'] . " AND course_id=" . $row['course_id'];
			$result2= mysql_query($sql2, $db);
		} 
	}

	//update forums_courses
	if (in_array('0', $_POST['courses'])) {
		//general course - used by all.  put one entry in forums_courses w/ course_id=0
		$sql	= "REPLACE INTO ".TABLE_PREFIX."forums_courses VALUES (" . $_POST['forum'] . ", 0)";
		$result	= mysql_query($sql, $db);
	} else {
		foreach ($_POST['courses'] as $course) {
			$sql	= "REPLACE INTO ".TABLE_PREFIX."forums_courses VALUES (" . $_POST['forum'] . "," . $course . ")";
			$result	= mysql_query($sql, $db);
		}
	}
	$msg->addFeedback('FORUM_UPDATED');
	header('Location: '.$_base_href.'admin/forums.php');
	exit;	
}

require(AT_INCLUDE_PATH.'header.inc.php'); 
echo '<h3>'._AT('edit_forum').'</h3><br />';

if (!($forum = @get_forum($_GET['forum']))) {
	//no such forum
	$msg->addError('FORUM_NOT_FOUND');
	$msg->printAll();
} else {
	$msg->printAll();

	$sql	= "SELECT * FROM ".TABLE_PREFIX."forums_courses WHERE forum_id=$forum[forum_id]";
	$result	= mysql_query($sql, $db);
	while ($row = mysql_fetch_assoc($result)) {
		$courses[] = $row['course_id'];		
	}
?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="form">
	<input type="hidden" name="edit_forum" value="true">
	<input type="hidden" name="forum" value="<?php echo $_REQUEST['forum']; ?>">
	<p>
	<table cellspacing="1" cellpadding="0" border="0" class="bodyline" summary="" align="center">
	<tr>
		<th colspan="2" class="cyan"><?php  echo _AT('forum'); ?></th>
	</tr>
	<tr>
		<td class="row1" align="right"><?php print_popup_help(AT_HELP_ADD_FORUM_MINI); ?><b><label for="title"><?php  echo _AT('title'); ?>:</label></b></td>
		<td class="row1"><input type="text" name="title" class="formfield" size="40" id="title" value="<?php echo $forum['title']?>" /></td>
	</tr>
	<tr><td height="1" class="row2" colspan="2"></td></tr>
	<tr>
		<td class="row1" valign="top" align="right"><b><label for="body"><?php echo _AT('description'); ?>:</label></b></td>
		<td class="row1"><textarea name="description" cols="45" rows="5" class="formfield" id="body" wrap="wrap"><?php echo $forum['description']?></textarea></td>
	</tr>
	<tr><td height="1" class="row2" colspan="2"></td></tr>
	<tr>
		<td class="row1" valign="top" align="right"><b><label for="body"><?php echo _AT('courses'); ?>:</label></b></td>
		<td class="row1"><select name="courses[]" multiple="multiple" size="5">
		<?php
			echo '<option value="0"';
			if ($courses[0] == 0) {
				echo ' selected="selected"';
			}
			echo '> '._AT('all').' </option>';
			
			$sql = "SELECT course_id, title FROM ".TABLE_PREFIX."courses ORDER BY title";
			$result = mysql_query($sql, $db);
			while ($row = mysql_fetch_assoc($result)) {
				if (in_array($row['course_id'], $courses) ) {
					echo '<option value="'.$row['course_id'].'" selected="selected">'.$row['title'].'</option>';		
				} else {
					echo '<option value="'.$row['course_id'].'">'.$row['title'].'</option>';
				}
			}
		?>
		</select>
		<br /><br /></td>
	</tr>
	<tr><td height="1" class="row2" colspan="2"></td></tr>
	<tr><td height="1" class="row2" colspan="2"></td></tr>
	<tr>
		<td class="row1" colspan="2" align="center"><br /><input type="submit" name="submit" value="<?php  echo _AT('submit'); ?> [Alt-s]" class="button" accesskey="s"> | <input type="submit" name="cancel" value="<?php  echo _AT('cancel'); ?>" class="button"></td>
	</tr>
	</table>
	</p>
	</form>
<?php
}

require(AT_INCLUDE_PATH.'footer.inc.php');
?>