<?php

class BackupField extends InputField
{

  static public $assets = array('js' => array('backup.js'), 'css' => array('backup.css'));

  public function input()
  {

    $input  = '<div id="backupStatus">Last backup created on : checking...<br>';
    $input .= '<ul><li>Getting data from the server.</li>';
    $input .= '<li>Please, be patient.</li></ul></div><br>';
    $input .= '<button type="button" class="btn btn-rounded btn-show clear-list" id="createBackup" title="create a new backup">';
    $input .= '<i class="fa fa-refresh"></i>Create new backup</button>';
    $input .= '<button type="button" class="btn btn-rounded" id="creatingBackup" title="creating a new backup - please, wait...">';
    $input .= '<i class="fa fa-refresh fa-spin"></i>Creating a new backup</button>';
    $input .= '<br><div id="backupList"><br><a>&nbsp;</a></div>';

    return $input;
  }

}

?>