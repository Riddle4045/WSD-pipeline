<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$url = array("https://www.youtube.com/watch?v=yIaOFZQUyQI","https://www.youtube.com/watch?v=ovI1UY11p_k");

foreach ( $url as $k){
            $cmd = "./youtube_wget.pl" + '"' + $k +'"' + "  kala_";
            exec($cmd);
}
