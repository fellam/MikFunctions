# MikFunctions

The '''MikFunctions''' extension defines an additional set of [[m:Help:Parser function|parser function]]s that provide dynamic functionality and cannot be cached.

NOTE: This extension currently works only on *NIX servers

For details click [here](https://www.mediawiki.org/Extension:MikFunctions) 

Please help me improving this project making a donation [here](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UBX4YGMGGWHEN)

# Dependencies

This extension is currently compatible with developed for MediaWiki 1.26.2 with at least Semantic
MediaWiki 2.4.5 and Page Forms 4.0.2 installed. Other version might work, but
are not tested.

# Installation

1. Download the package. Unpack the folder inside /extensions (so that the files
   are in /extensions/MikFunctions, rename the folder if necessary).
   
2. Create a directory named "'''seqences'''" in the extension folder 
   and grant write permissions for the wiki directory owner.  

	Example on linux: 
		mkdir ../extensions/MikFunctions/sequences;
		chown apache:apache ../extensions/MikFunctions/sequences;

3. In your LocalSettings.php, add the following line to the end of the file:

   require_once("$IP/extensions/MikFunctions/MikFunctions.php");
   
