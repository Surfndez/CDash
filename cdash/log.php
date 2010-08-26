<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id$
  Language:  PHP
  Date:      $Date$
  Version:   $Revision$

  Copyright (c) 2002 Kitware, Inc.  All rights reserved. 
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
require_once("cdash/defines.php");
require_once("cdash/pdocore.php");
require_once("models/errorlog.php");

/** Add information to the log file */
function add_log($text, $function, $type=LOG_INFO, $projectid=0, $buildid=0,
                 $resourcetype=0, $resourceid=0)
{
  global $CDASH_LOG_FILE;
  $logFile = $CDASH_LOG_FILE;

  if(!file_exists(dirname($logFile)))
    {
    $paths = explode(PATH_SEPARATOR, get_include_path());
    // Search the include path for the log file
    foreach($paths as $path)
      {
      if(file_exists(dirname("$path/$CDASH_LOG_FILE")))
        {
        $logFile = "$path/$CDASH_LOG_FILE";
        break;
        }
      }
    }

  if(strlen($text)==0)
  {
    return;
  }
  $error = "";
  if($type != LOG_TESTING)
  {
    $error = "[".date(FMT_DATETIME)."]";
  }

  // This is parsed by the testing
  switch($type)
  {
    case LOG_INFO: $error.="[INFO]"; break;
    case LOG_WARNING: $error.="[WARNING]"; break;
    case LOG_ERR: $error.="[ERROR]"; break;
    case LOG_TESTING: $error.="[TESTING]";break;
  }
  $error .= "[pid=".getmypid()."]";
  $error .= "(".$function."): ".$text."\n";


  $log_pre_exists = file_exists($logFile);

  error_log($error, 3, $logFile);

  // If we just created the logFile, then give it group write permissions
  // so that command-line invocations of CDash functions can also write to
  // the same log file.
  //
  if (!$log_pre_exists)
    {
    chmod($logFile, 0664);
    }

  // Insert in the database
  if($type == LOG_WARNING || $type==LOG_ERR)
  {
    $ErrorLog = new ErrorLog;
    $ErrorLog->ProjectId = $projectid;
    $ErrorLog->BuildId = $buildid;
    switch($type)
    {
      case LOG_INFO: $ErrorLog->Type = 6;
      case LOG_WARNING: $ErrorLog->Type = 5;
      case LOG_ERR: $ErrorLog->Type = 4;
    }
    $ErrorLog->Description = "(".$function."): ".$text;
    $ErrorLog->ResourceType = $resourcetype;
    $ErrorLog->ResourceId = $resourceid;

    $ErrorLog->Insert();

    // Clean the log more than 10 days
    $ErrorLog->Clean(10);
  }
}
?>