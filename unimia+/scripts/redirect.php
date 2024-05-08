<?php

function redirect($url, $code = 303) {
   header('Location: ' . $url, true, $code);
   
   die();
}