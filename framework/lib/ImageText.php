<?php
header("Content-type: image/png");
// Your email address which will be shown in the image
$email    =    "you@yourdomain.com";
$length    =    (strlen($email)*8);
$im = @ImageCreate ($length, 20)
	or die ("Kann keinen neuen GD-Bild-Stream erzeugen");
$background_color = ImageColorAllocate ($im, 255, 255, 255); // White: 255,255,255
$text_color = ImageColorAllocate ($im, 55, 103, 122);
imagestring($im, 3,5,2,$email, $text_color);
imagepng ($im);
?>