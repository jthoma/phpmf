<?php

/**
 * @package MariaFramework
 * @description mf_portal
 * @author Jiju Thomas Mathew
 */

function sessions()
{
  if (session_id() == '' || !isset($_SESSION)) {
    // session isn't started
    session_start();
  }
}
