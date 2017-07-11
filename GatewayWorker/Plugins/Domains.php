<?php

namespace Plugins;

class Domains
{
	public function getDomainNames()
    	{
        	//return "112223333";
		$myfile = fopen(__DIR__ . '/word.txt', "r") or die("Unable to open file!");
		return fread($myfile,filesize(__DIR__ . '/word.txt'));
		fclose($myfile);
    	}
}
