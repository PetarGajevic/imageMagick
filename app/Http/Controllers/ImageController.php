<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Image;
use App\User;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class ImageController extends Controller
{
    //


    public function upload1(Request $request){
      $file = $request->file('file');
      $imageName =  $file->getClientOriginalName();
      $filename = pathinfo($imageName, PATHINFO_FILENAME);
      
      $extension =  $request->file('file')->getClientOriginalExtension();
     // dd($extension);
      $image = $filename . "_" . time() . ".".$extension;
       $file->move('design/', $image); 

       $tshirt = new \Imagick(public_path('image/Iphone-II-Pro-samo maska.png')); 
      $logo = new \Imagick(public_path("design/" . $image));
    
      $logo->resizeImage(400, 400, \Imagick::FILTER_LANCZOS, 1, TRUE);

      $tshirt->setImageFormat('png');

      $colorString = User::getAverageColorString($tshirt);
      $creases = new \Imagick();
      $creases->newpseudoimage(
      $tshirt->getImageWidth(),
      $tshirt->getImageHeight(), 
      "XC:".$colorString
);

$creases->compositeimage($tshirt, \Imagick::COMPOSITE_DIFFERENCE, 0, 0);
$creases->setImageFormat('png');
//We need the image negated for the maths to work later. 
$creases->negateimage(true);
//We also want "no crease" to equal 50% gray later
 /* $creases->brightnessContrastImage(-45, 0); */  //This isn't in Imagick head yet, but is more sensible than the modulate function.
 $creases->modulateImage(50, 100, 100);  

//Copy the logo into an image the same size as the shirt image
//to make life easier
$logoCentre = new \Imagick();
$logoCentre->newpseudoimage(
   $tshirt->getImageWidth(),
   $tshirt->getImageHeight(),
   "XC:none"
);
$logoCentre->setImageFormat('png');
$logoCentre->compositeimage($logo, \Imagick::COMPOSITE_SRCOVER, 330, 230);

//Save a copy of the tshirt sized logo
$logoCentreMask = clone $logoCentre;

//Blend the creases with the logo
$logoCentre->compositeimage($creases, \Imagick::COMPOSITE_MODULATE, 0, 0);

//Mask the logo so that only the pixels under the logo come through
$logoCentreMask->compositeimage($logoCentre, \Imagick::COMPOSITE_SRCIN, 0, 0);

//Composite the creased logo onto the shirt
$tshirt->compositeimage($logoCentreMask, \Imagick::COMPOSITE_DEFAULT, 0, 0);

//And Robert is your father's brother
header("Content-Type: image/png");

echo $tshirt->getImageBlob();
    }

    
    public function uploadMockup(Request $request){

      $file = $request->file('file');
      $imageName =  $file->getClientOriginalName();
      $imageName = preg_replace('/\s+/', '', $imageName);
      $filename = pathinfo($imageName, PATHINFO_FILENAME);
      
      $extension =  $request->file('file')->getClientOriginalExtension();
     // dd($extension);
      $image = $filename . "_" . time() . ".".$extension;
       $file->move('design/', $image); 

       $imageName1 = "/" .  $image; 


/* 
       $src1 = new \Imagick(public_path("design". $imageName1));
       $src1->resizeImage(500, null,\Imagick::FILTER_LANCZOS,1); 
       $src1->writeImage(public_path("design". $imageName1));
      $src2 = new \Imagick(public_path("\image\Iphone-II-Pro-Bezpozadine1.png"));
      $src3 = new \Imagick(public_path("\image\Iphone-II-Pro.jpg"));
      
      
       $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
      
       $process5 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\Iphone-II-Pro.jpg ^
       -channel A -blur 0x8
       -compose hardlight
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map-phone.png
        ');

       dd(); */

 $src1 = new \Imagick(public_path("design". $imageName1));
 $src1->resizeImage(500, null,\Imagick::FILTER_LANCZOS,1); 
 $src1->writeImage(public_path("design". $imageName1));
$src2 = new \Imagick(public_path("\image\Iphone-II-Pro-Bezpozadine1.png"));
$src3 = new \Imagick(public_path("\image\Iphone-II-Pro.jpg"));


 $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 

 $process5 = new Process('magick convert ^
 C:\xampp\htdocs\www\imageMagick\public\image\Iphone-II-Pro.jpg ^
 -channel A -blur 0x8
 -compose hardlight
 C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map-phone.png
  ');

  /* Makao sam komandu -separate proces 5   -colorspace gray -auto-level ^
 -blur 0x3 ^
 -contrast-stretch 0,50%% ^
 -depth 16 ^  -negate  -channel A -blur 0x8*/

$process5->run();
if (!$process5->isSuccessful()) {
 throw new ProcessFailedException($process5);
}
 echo $process5->getOutput();
 echo '<img src="\image\ms_light_map-phone.png">';

 $process6 = new Process('magick convert ^
 C:\xampp\htdocs\www\imageMagick\public\design'. $imageName1. ' ^
 -channel matte -separate ^
 C:\xampp\htdocs\www\imageMagick\public\image\ms_logo_displace_mask_phone.png
  ');

$process6->run();
if (!$process6->isSuccessful()) {
 throw new ProcessFailedException($process6);
}
 echo $process6->getOutput();
 echo '<img src="\image\ms_logo_displace_mask_phone.png">';

 $process7 = new Process('magick convert ^
 C:\xampp\htdocs\www\imageMagick\public\design'. $imageName1. ' ^
 C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map-phone.png ^
 -geometry -250-150 ^
 -compose Multiply -composite ^
 C:\xampp\htdocs\www\imageMagick\public\image\ms_logo_displace_mask_phone.png ^
 -compose CopyOpacity -composite ^
 C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map_logo_phone.png
 ');

$process7->run();
if (!$process7->isSuccessful()) {
throw new ProcessFailedException($process7);
}
echo $process7->getOutput();
echo '<img src="\image\ms_light_map_logo_phone.png">';

$src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
$src = new \Imagick(public_path("\image\ms_light_map_logo_phone.png"));
$src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 250, 150);
$src2->writeImage(public_path("image/output.png"));
echo '<img src="data:image/jpg;base64,'.base64_encode($src2->getImageBlob()).'" alt="" />'; 
/* $src1 = new \Imagick(public_path("\image\ms_light_map_logo_phone.png")); */


$process8 = new Process('magick convert ^
C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map-phone.png ^
C:\xampp\htdocs\www\imageMagick\public\image\output.png ^
-compose ATop -composite ^

-depth 16 ^
C:\xampp\htdocs\www\imageMagick\public\image\ms_product_phone.png
');

$process8->run();
if (!$process8->isSuccessful()) {
throw new ProcessFailedException($process8);
}
echo $process8->getOutput();
echo '<img src="\image\ms_product_phone.png">';



dd();
-geometry +250+150  ^
$src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
$src = new \Imagick(public_path("\image\ms_light_map_logo_phone.png"));
$src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 250, 150);
$src2->writeImage(public_path("image/output.png"));
echo '<img src="data:image/jpg;base64,'.base64_encode($src2->getImageBlob()).'" alt="" />'; 
$src1 = new \Imagick(public_path("\image\ms_light_map_logo_phone.png"));

$src3->compositeImage($src2, \Imagick::COMPOSITE_SOFTLIGHT           ,0,0);
$src3->writeImage(public_path("image/output-mask.png"));
echo '<img src="data:image/jpg;base64,'.base64_encode($src3->getImageBlob()).'" alt="" />';

 dd();
/*  $process1 = new Process('magick convert   C:\xampp\htdocs\www\imageMagick\public\image\Iphone-II-Pro-Bezpozadine.png C:\xampp\htdocs\www\imageMagick\public\image\output.png
 -compose Multiply
 C:\xampp\htdocs\www\imageMagick\public\image\output-mask1-1.jpg 
 ');

$process1->run();
if (!$process1->isSuccessful()) {
throw new ProcessFailedException($process1);
} 
echo $process1->getOutput();
echo '<img src="\image\output-mask1-1.jpg">';
 */



$src3->compositeImage($src2, \Imagick::COMPOSITE_DISSOLVE ,0,0);
$src3->writeImage(public_path("image/output-mask.png"));
echo '<img src="data:image/jpg;base64,'.base64_encode($src3->getImageBlob()).'" alt="" />';
dd();
        /* Maska proba 

       $process2 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '  C:\xampp\htdocs\www\imageMagick\public\image\Iphone-II-Pro-Bezpozadine.png
       -background black -gravity center 
       -flatten  C:\xampp\htdocs\www\imageMagick\public\image\result.png
        ');
  
   $process2->run();
   if (!$process2->isSuccessful()) {
       throw new ProcessFailedException($process2);
   }
       echo $process2->getOutput();
       echo '<img src="\image\result.png">';
*/
/* 
$s1=    new \Imagick(public_path('image/Iphone-II-Pro-Bezpozadine.png'));
$s2=    new \Imagick(public_path('design'.$imageName1));
$s2->setImageFormat ('png');
$s2->setImageBackgroundColor("transparent"); // <= Here
$s2->vignetteImage(20, 20, 40, - 20); 
$s2->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$s1->compositeImage($s2, \Imagick::COMPOSITE_DEFAULT,120,120, \Imagick::CHANNEL_ALPHA);
echo '<img src="data:image/jpg;base64,'.base64_encode($s1->getImageBlob()).'" alt="" />';
dd();
 */
 $process1 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\design'. $imageName1.'
-resize 800x800 C:\xampp\htdocs\www\imageMagick\public\design'. $imageName1.'
 ');

$process1->run();
if (!$process1->isSuccessful()) {
throw new ProcessFailedException($process1);
}  

/*  $process2 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\image\Iphone-II-Pro-Bezpozadine.png 
-resize 1500x1500 C:\xampp\htdocs\www\imageMagick\public\image\Iphone-II-Pro-Bezpozadine.png
 ');

$process2->run();
if (!$process2->isSuccessful()) {
throw new ProcessFailedException($process2);
} 
  */

   
$process3 = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '    C:\xampp\htdocs\www\imageMagick\public\image\Iphone-II-Pro-Bezpozadine.png ^
-background black -gravity center -compose over -composite C:\xampp\htdocs\www\imageMagick\public\image\result1.png
 ');

$process3->run();
if (!$process3->isSuccessful()) {
throw new ProcessFailedException($process3);
}
echo $process3->getOutput();
echo '<img src="\image\result1.png">';


       dd();

       $imagick2 = new \Imagick(public_path('image/Iphone-II-Pro-Bez pozadine.png'));
       $im = new \Imagick(public_path('design/'. $image));
       $im->resizeImage(500, 500, \Imagick::FILTER_LANCZOS, 1, TRUE);
     
       $im->addImage($imagick2);
       $im->setImageFormat('png');
       $result = $im->mergeImageLayers(\Imagick::LAYERMETHOD_OPTIMIZE );
       echo '<img src="data:image/jpg;base64,'.base64_encode($result->getImageBlob()).'" alt="" />';
       
       dd();

       $tshirt = new \Imagick(public_path('image/Iphone-II-Pro-Bez pozadine.png')); 
      $logo = new \Imagick(public_path("design/" . $image));
    
      $logo->resizeImage(400, 400, \Imagick::FILTER_LANCZOS, 1, TRUE);

      $tshirt->setImageFormat('png');

      $colorString = User::getAverageColorString($tshirt);
      $creases = new \Imagick();
      $creases->newpseudoimage(
      $tshirt->getImageWidth(),
      $tshirt->getImageHeight(), 
      "XC:".$colorString
);

$creases->compositeimage($tshirt, \Imagick::COMPOSITE_DIFFERENCE, 0, 0);
$creases->setImageFormat('png');
//We need the image negated for the maths to work later. 
$creases->negateimage(true);
//We also want "no crease" to equal 50% gray later
 /* $creases->brightnessContrastImage(-45, 0); */  //This isn't in Imagick head yet, but is more sensible than the modulate function.
 $creases->modulateImage(50, 100, 100);  

//Copy the logo into an image the same size as the shirt image
//to make life easier
$logoCentre = new \Imagick();
$logoCentre->newpseudoimage(
   $tshirt->getImageWidth(),
   $tshirt->getImageHeight(),
   "XC:none"
);
$logoCentre->setImageFormat('png');
$logoCentre->compositeimage($logo, \Imagick::COMPOSITE_OVER, 330, 230);

//Save a copy of the tshirt sized logo
$logoCentreMask = clone $logoCentre;

//Blend the creases with the logo
$logoCentre->compositeimage($creases, \Imagick::COMPOSITE_MODULATE, 0, 0);

//Mask the logo so that only the pixels under the logo come through
$logoCentreMask->compositeimage($logoCentre, \Imagick::COMPOSITE_SRCIN, 0, 0);

//Composite the creased logo onto the shirt
$tshirt->compositeimage($logoCentreMask, \Imagick::COMPOSITE_DEFAULT, 0, 0);

//And Robert is your father's brother
header("Content-Type: image/png");

echo $tshirt->getImageBlob();
    }












    public function uploadMockup1(Request $request){

      $file = $request->file('file');
      $imageName =  $file->getClientOriginalName();
      $filename = pathinfo($imageName, PATHINFO_FILENAME);
      
      $extension =  $request->file('file')->getClientOriginalExtension();
     // dd($extension);
      $image = $filename . "_" . time() . ".".$extension;
       $file->move('design/', $image); 


   

       $tshirt = new \Imagick(public_path('image/Black-Tee-Shirt-Blank-PC61.jpg')); 
      /* $tshirt = new \Imagick(public_path('image/White-Tee-Shirt-Blank-PC61.jpg')); */
      $logo = new \Imagick(public_path("design/" . $image));
     /*  $tshirt->resizeImage(1000, 1000, \Imagick::FILTER_LANCZOS, 1, TRUE);*/
      $logo->resizeImage(300, 300, \Imagick::FILTER_LANCZOS, 1, TRUE);
 
      $tshirt->setImageFormat('png');

      $colorString = User::getAverageColorString($tshirt);
      $creases = new \Imagick();
      $creases->newpseudoimage(
      $tshirt->getImageWidth(),
      $tshirt->getImageHeight(), 
      "XC:".$colorString
);

$creases->compositeimage($tshirt, \Imagick::COMPOSITE_DIFFERENCE, 0, 0);
$creases->setImageFormat('png');
//We need the image negated for the maths to work later. 
$creases->negateimage(true);
//We also want "no crease" to equal 50% gray later
//$creases->brightnessContrastImage(-50, 0); //This isn't in Imagick head yet, but is more sensible than the modulate function.
$creases->modulateImage(50, 100, 100);

//Copy the logo into an image the same size as the shirt image
//to make life easier
$logoCentre = new \Imagick();
$logoCentre->newpseudoimage(
   $tshirt->getImageWidth(),
   $tshirt->getImageHeight(),
   "XC:none"
);
$logoCentre->setImageFormat('png');
$logoCentre->compositeimage($logo, \Imagick::COMPOSITE_OVERLAY, 350, 375);

//Save a copy of the tshirt sized logo
$logoCentreMask = clone $logoCentre;

//Blend the creases with the logo
 $logoCentre->compositeimage($creases, \Imagick::COMPOSITE_MODULATE, 0, 0); 

//Mask the logo so that only the pixels under the logo come through
/*  $logoCentreMask->compositeimage($logoCentre, \Imagick::COMPOSITE_SRCIN, 0, 0);  */

//Composite the creased logo onto the shirt
$tshirt->compositeimage($logoCentreMask, \Imagick::COMPOSITE_DEFAULT, 0, 0);

//And Robert is your father's brother
header("Content-Type: image/png");
echo $tshirt->getImageBlob();
    }

    public function uploadMockup2(Request $request){
      $file = $request->file('file');
      $imageName =  $file->getClientOriginalName();
      $filename = pathinfo($imageName, PATHINFO_FILENAME);
      
      $extension =  $request->file('file')->getClientOriginalExtension();
      $image = $filename . "_" . time() . ".".$extension;
       $file->move('design/', $image); 

       $imagick2 = new \Imagick(public_path('image/White-Tee-Shirt-Blank-PC61.jpg'));
    $im = new \Imagick(public_path('design/'. $image));
    $im->resizeImage(100, 100, \Imagick::FILTER_LANCZOS, 1, TRUE);
    $imagick2->addImage($im);
    $imagick2->setImageFormat('png');
    $result = $imagick2->mergeImageLayers(\Imagick::LAYERMETHOD_OPTIMIZE );
	echo '<img src="data:image/jpg;base64,'.base64_encode($result->getImageBlob()).'" alt="" />';


    }

    /* public function uploadMockup3(Request $request){

      $file = $request->file('file');
      $imageName =  $file->getClientOriginalName();
      $imageName = preg_replace('/\s+/', '', $imageName);
      $filename = pathinfo($imageName, PATHINFO_FILENAME);
      
      $extension =  $request->file('file')->getClientOriginalExtension();
     // dd($extension);
      $image = $filename . "_" . time() . ".".$extension;
       $file->move('design/', $image); 

       $process = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\image\tshirt-16.jpg[403x422+404+881] 
       -colorspace gray 
       -blur 10x250 
       -auto-level
       C:\xampp\htdocs\www\imageMagick\public\image\displace_map.png
        ');
  
   $process->run();
   if (!$process->isSuccessful()) {
       throw new ProcessFailedException($process);
   }
       echo $process->getOutput();
       echo '<img src="\image\displace_map.png">';

       $imageName1 = "/" .  $image; 

       $process1 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
    
       -resize 300x300
       C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
       '); 
       
    $process1->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process1);    
   } 


       $process2 = new Process('magick convert 
       C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
       -bordercolor transparent -border 12x12 -thumbnail 403x422 
       C:\xampp\htdocs\www\imageMagick\public\image\ms_temp.png
        ');
  
   $process2->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process2);
   }
       echo $process2->getOutput();
       echo '<img src="\image\ms_temp.png">';

      

       $process3 = new Process('magick convert 
       C:\xampp\htdocs\www\imageMagick\public\image\tshirt-16.jpg[403x422+404+881] 
       -colorspace gray -blur 10x250 -auto-level 
       -depth 16 
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displace_map.png
        ');
  
   $process3->run();
   if (!$process3->isSuccessful()) {
       throw new ProcessFailedException($process3);
   }
       echo $process3->getOutput();
       echo '<img src="\image\ms_displace_map.png">';
      
       $process4 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_temp.png ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displace_map.png ^
       -alpha set -virtual-pixel transparent ^
       -compose displace -set option:$compose:args -5x-5 -composite ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png
     
        ');
  
   $process4->run();
   if (!$process4->isSuccessful()) {
       throw new ProcessFailedException($process4);
   }
       echo $process4->getOutput();
       echo '<img src="\image\ms_displaced_logo.png">';

       
       $process5 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\tshirt-16.jpg[403x422+404+881] ^
       -colorspace gray -auto-level ^
       -blur 0x3 ^
       -contrast-stretch 0,50%% ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map.png
        ');

         Makao sam komandu -separate proces 5 
  
   $process5->run();
   if (!$process5->isSuccessful()) {
       throw new ProcessFailedException($process5);
   }
       echo $process5->getOutput();
       echo '<img src="\image\ms_light_map.png">';
       
       $process6 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png ^
       -channel matte -separate ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_logo_displace_mask.png
        ');
  
   $process6->run();
   if (!$process6->isSuccessful()) {
       throw new ProcessFailedException($process6);
   }
       echo $process6->getOutput();
       echo '<img src="\image\ms_logo_displace_mask.png">';
       
       $process7 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map.png ^
       -compose Multiply -composite ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_logo_displace_mask.png ^
       -compose CopyOpacity -composite ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map_logo.png
       ');
 
  $process7->run();
  if (!$process7->isSuccessful()) {
      throw new ProcessFailedException($process7);
  }
      echo $process7->getOutput();
      echo '<img src="\image\ms_light_map_logo.png">';
      


      

       $process8 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\tshirt-16.jpg ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map_logo.png ^
       -geometry +404+881 ^
       -compose over -composite ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_product.png
       ');
 
  $process8->run();
  if (!$process8->isSuccessful()) {
      throw new ProcessFailedException($process8);
  }
      echo $process8->getOutput();
      echo '<img src="\image\ms_product.png">';

      dd();
      

    
      
      

    } */

    public function uploadMockup3(Request $request){
      $file = $request->file('file');
      $imageName =  $file->getClientOriginalName();
      $imageName = preg_replace('/\s+/', '', $imageName);
      $filename = pathinfo($imageName, PATHINFO_FILENAME);
      
      $extension =  $request->file('file')->getClientOriginalExtension();
     // dd($extension);
      $image = $filename . "_" . time() . ".".$extension;
       $file->move('design/', $image); 

       $process0 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\image\U-one-16.jpg 
       -resize 1500x2500
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-16.jpg 
        ');
        $process0->run();
        if (!$process0->isSuccessful()) {
            throw new ProcessFailedException($process0);
        } 

       $process = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\image\U-one-16.jpg[403x422+584+601] 
       -colorspace gray 
       -blur 10x250 
       -auto-level
       C:\xampp\htdocs\www\imageMagick\public\image\displace_map.png
        ');
  
   $process->run();
   if (!$process->isSuccessful()) {
       throw new ProcessFailedException($process);
   }
       echo $process->getOutput();
       echo '<img src="\image\displace_map.png">';

       $imageName1 = "/" .  $image; 

       $process1 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
    
       -resize 300x300
       C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
       '); 
       
    $process1->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process1);    
   } 


       $process2 = new Process('magick convert 
       C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
       -bordercolor transparent -border 12x12 -thumbnail 403x422 
       C:\xampp\htdocs\www\imageMagick\public\image\ms_temp.png
        ');
  
   $process2->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process2);
   }
       echo $process2->getOutput();
       echo '<img src="\image\ms_temp.png">';

      

       $process3 = new Process('magick convert 
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-16.jpg[403x422+584+601] 
       -colorspace gray -blur 10x250 -auto-level 
       -depth 16 
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displace_map.png
        ');
  
   $process3->run();
   if (!$process3->isSuccessful()) {
       throw new ProcessFailedException($process3);
   }
       echo $process3->getOutput();
       echo '<img src="\image\ms_displace_map.png">';
      
       $process4 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_temp.png ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displace_map.png ^
       -alpha set -virtual-pixel transparent ^
       -compose displace -set option:compose:args -5x-5 -composite ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png
     
        ');
  
   $process4->run();
   if (!$process4->isSuccessful()) {
       throw new ProcessFailedException($process4);
   }
       echo $process4->getOutput();
       echo '<img src="\image\ms_displaced_logo.png">';

       
       $process5 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-16.jpg[403x422+584+601] ^
       -colorspace gray -auto-level ^
       -blur 0x3 ^
       -contrast-stretch 0,50%% ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map.png
        ');

        /* Makao sam komandu -separate proces 5 */
  
   $process5->run();
   if (!$process5->isSuccessful()) {
       throw new ProcessFailedException($process5);
   }
       echo $process5->getOutput();
       echo '<img src="\image\ms_light_map.png">';
       
       $process6 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png ^
       -channel matte -separate ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_logo_displace_mask.png
        ');
  
   $process6->run();
   if (!$process6->isSuccessful()) {
       throw new ProcessFailedException($process6);
   }
       echo $process6->getOutput();
       echo '<img src="\image\ms_logo_displace_mask.png">';
       
       $process7 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map.png ^
       -compose Multiply -composite ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_logo_displace_mask.png ^
       -compose CopyOpacity -composite ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map_logo.png
       ');
 
  $process7->run();
  if (!$process7->isSuccessful()) {
      throw new ProcessFailedException($process7);
  }
      echo $process7->getOutput();
      echo '<img src="\image\ms_light_map_logo.png">';
      


      

       $process8 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-16.jpg ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map_logo.png ^
       -geometry +584+601 ^
       -compose over -composite ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_product.png
       ');
 
  $process8->run();
  if (!$process8->isSuccessful()) {
      throw new ProcessFailedException($process8);
  }
      echo $process8->getOutput();
      echo '<img src="\image\ms_product.png">';

    }

    public function uploadMockup4(Request $request){

      $file = $request->file('file');
      $imageName =  $file->getClientOriginalName();
      $imageName = preg_replace('/\s+/', '', $imageName);
      $filename = pathinfo($imageName, PATHINFO_FILENAME);
      
      $extension =  $request->file('file')->getClientOriginalExtension();
     // dd($extension);
      $image = $filename . "_" . time() . ".".$extension;
       $file->move('design/', $image); 

      /*  $process0 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\image\U-one-13.jpg 
       -resize 1500x2500
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-13.jpg 
        ');
        $process0->run();
        if (!$process0->isSuccessful()) {
            throw new ProcessFailedException($process0);
        } */

       $process = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\image\U-one-13.jpg[403x422+584+601] 
       -colorspace gray 
       -blur 10x250 
       -auto-level
       C:\xampp\htdocs\www\imageMagick\public\image\displace_map.png
        ');
  
   $process->run();
   if (!$process->isSuccessful()) {
       throw new ProcessFailedException($process);
   }
       echo $process->getOutput();
       echo '<img src="\image\displace_map.png">';

       $imageName1 = "/" .  $image; 

       $process1 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
       -resize 300x300
       C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
       '); 
       
    $process1->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process1);    
   } 


       $process2 = new Process('magick convert 
       C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
       -bordercolor transparent -border 12x12 -thumbnail 403x422 
       C:\xampp\htdocs\www\imageMagick\public\image\ms_temp.png
        ');
  
   $process2->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process2);
   }
       echo $process2->getOutput();
       echo '<img src="\image\ms_temp.png">';

      

       $process3 = new Process('magick convert 
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-13.jpg[403x422+584+601] 
       -colorspace gray -blur 10x250 -auto-level 
       -depth 16 
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displace_map.png
        ');
  
   $process3->run();
   if (!$process3->isSuccessful()) {
       throw new ProcessFailedException($process3);
   }
       echo $process3->getOutput();
       echo '<img src="\image\ms_displace_map.png">';
      
       $process4 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_temp.png ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displace_map.png ^
       -alpha set -virtual-pixel transparent ^
       -compose displace -set option:compose:args -5x-5 -composite ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png
     
        ');
  
   $process4->run();
   if (!$process4->isSuccessful()) {
       throw new ProcessFailedException($process4);
   }
       echo $process4->getOutput();
       echo '<img src="\image\ms_displaced_logo.png">';

       
       $process5 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-13.jpg[403x422+584+601] ^
       -colorspace gray -auto-level ^
       -blur 0x3 ^
       -contrast-stretch 0,50%% ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map.png
        ');

        /* Makao sam komandu -separate proces 5 */
  
   $process5->run();
   if (!$process5->isSuccessful()) {
       throw new ProcessFailedException($process5);
   }
       echo $process5->getOutput();
       echo '<img src="\image\ms_light_map.png">';
       
       $process6 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png ^
       -channel matte -separate ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_logo_displace_mask.png
        ');
  
   $process6->run();
   if (!$process6->isSuccessful()) {
       throw new ProcessFailedException($process6);
   }
       echo $process6->getOutput();
       echo '<img src="\image\ms_logo_displace_mask.png">';
       
       $process7 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map.png ^
       -compose Multiply -composite ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_logo_displace_mask.png ^
       -compose CopyOpacity -composite ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map_logo.png
       ');
 
  $process7->run();
  if (!$process7->isSuccessful()) {
      throw new ProcessFailedException($process7);
  }
      echo $process7->getOutput();
      echo '<img src="\image\ms_light_map_logo.png">';
      


      

       $process8 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-13.jpg ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map_logo.png ^
       -geometry +584+601 ^
       -compose over -composite ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_product.png
       ');
 
  $process8->run();
  if (!$process8->isSuccessful()) {
      throw new ProcessFailedException($process8);
  }
      echo $process8->getOutput();
      echo '<img src="\image\ms_product.png">';

      dd();
      
      

    }

    public function uploadMockup5(Request $request){
      $file = $request->file('file');
      $imageName =  $file->getClientOriginalName();
      $imageName = preg_replace('/\s+/', '', $imageName);
      $filename = pathinfo($imageName, PATHINFO_FILENAME);
      
      $extension =  $request->file('file')->getClientOriginalExtension();
     // dd($extension);
      $image = $filename . "_" . time() . ".".$extension;
       $file->move('design/', $image); 

       $process0 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\image\U-one-26.jpg 
       -resize 1500x2500
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-26.jpg 
        ');
        $process0->run();
        if (!$process0->isSuccessful()) {
            throw new ProcessFailedException($process0);
        }

       $process = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\image\U-one-26.jpg[403x422+584+601] 
       -colorspace gray 
       -blur 10x250 
       -auto-level
       C:\xampp\htdocs\www\imageMagick\public\image\displace_map.png
        ');
  
   $process->run();
   if (!$process->isSuccessful()) {
       throw new ProcessFailedException($process);
   }
       echo $process->getOutput();
       echo '<img src="\image\displace_map.png">';

       $imageName1 = "/" .  $image; 

       $process1 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
    
       -resize 300x300
       C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
       '); 
       
    $process1->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process1);    
   } 


       $process2 = new Process('magick convert 
       C:\xampp\htdocs\www\imageMagick\public\design' . $imageName1 . '
       -bordercolor transparent -border 12x12 -thumbnail 403x422 
       C:\xampp\htdocs\www\imageMagick\public\image\ms_temp.png
        ');
  
   $process2->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process2);
   }
       echo $process2->getOutput();
       echo '<img src="\image\ms_temp.png">';

      

       $process3 = new Process('magick convert 
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-26.jpg[403x422+584+601] 
       -colorspace gray -blur 10x250 -auto-level 
       -depth 16 
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displace_map.png
        ');
  
   $process3->run();
   if (!$process3->isSuccessful()) {
       throw new ProcessFailedException($process3);
   }
       echo $process3->getOutput();
       echo '<img src="\image\ms_displace_map.png">';
      
       $process4 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_temp.png ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displace_map.png ^
       -alpha set -virtual-pixel transparent ^
       -compose displace -set option:compose:args -5x-5 -composite ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png
     
        ');
  
   $process4->run();
   if (!$process4->isSuccessful()) {
       throw new ProcessFailedException($process4);
   }
       echo $process4->getOutput();
       echo '<img src="\image\ms_displaced_logo.png">';

       
       $process5 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-26.jpg[403x422+584+601] ^
       -colorspace gray -auto-level ^
       -blur 0x3 ^
       -contrast-stretch 0,50%% ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map.png
        ');

        /* Makao sam komandu -separate proces 5 */
  
   $process5->run();
   if (!$process5->isSuccessful()) {
       throw new ProcessFailedException($process5);
   }
       echo $process5->getOutput();
       echo '<img src="\image\ms_light_map.png">';
       
       $process6 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png ^
       -channel matte -separate ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_logo_displace_mask.png
        ');
  
   $process6->run();
   if (!$process6->isSuccessful()) {
       throw new ProcessFailedException($process6);
   }
       echo $process6->getOutput();
       echo '<img src="\image\ms_logo_displace_mask.png">';
       
       $process7 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_displaced_logo.png ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map.png ^
       -compose Multiply -composite ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_logo_displace_mask.png ^
       -compose CopyOpacity -composite ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map_logo.png
       ');
 
  $process7->run();
  if (!$process7->isSuccessful()) {
      throw new ProcessFailedException($process7);
  }
      echo $process7->getOutput();
      echo '<img src="\image\ms_light_map_logo.png">';
      


      

       $process8 = new Process('magick convert ^
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-26.jpg ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map_logo.png ^
       -geometry +584+601 ^
       -compose over -composite ^
       -depth 16 ^
       C:\xampp\htdocs\www\imageMagick\public\image\ms_product.png
       ');
 
  $process8->run();
  if (!$process8->isSuccessful()) {
      throw new ProcessFailedException($process8);
  }
      echo $process8->getOutput();
      echo '<img src="\image\ms_product.png">';
      
      
     /*  echo '<img src="data:image/jpg;base64,'.base64_encode($a1->getImageBlob()).'" alt="" />';   */
    }

    public function upload(Request $request){
      
        $file = $request->file('file');
        $imageName =  $file->getClientOriginalName();
        $imageName = preg_replace('/\s+/', '', $imageName);
        $filename = pathinfo($imageName, PATHINFO_FILENAME);
        
        $extension =  $request->file('file')->getClientOriginalExtension();
       // dd($extension);
        $image = $filename . "_" . time() . ".".$extension;
         $file->move('design/', $image); 
  
         $imageName1 = "/" .  $image; 
  
         $path = public_path();
  /* 
         $src1 = new \Imagick(public_path("design". $imageName1));
         $src1->resizeImage(500, null,\Imagick::FILTER_LANCZOS,1); 
         $src1->writeImage(public_path("design". $imageName1));
        $src2 = new \Imagick(public_path("\image\Iphone-II-Pro-Bezpozadine1.png"));
        $src3 = new \Imagick(public_path("\image\Iphone-II-Pro.jpg"));
        
        
         $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
        
         $process5 = new Process('magick convert ^
         C:\xampp\htdocs\www\imageMagick\public\image\Iphone-II-Pro.jpg ^
         -channel A -blur 0x8
         -compose hardlight
         C:\xampp\htdocs\www\imageMagick\public\image\ms_light_map-phone.png
          ');
  
         dd(); */

        

       
          /* Makao sam komandu -separate proces 5   -colorspace gray -auto-level ^
         -blur 0x3 ^
         -contrast-stretch 0,50%% ^
         -depth 16 ^  -negate  -channel A -blur 0x8*/
        
     
        
  
   $src1 = new \Imagick(public_path("design". $imageName1));
   $src1->resizeImage(500, null,\Imagick::FILTER_LANCZOS,1); 
   $src1->writeImage(public_path("design". $imageName1));
  $src2 = new \Imagick(public_path("\image\Iphone-II-Pro-Bezpozadine1.png"));
  $src3 = new \Imagick(public_path("\image\Iphone-II-Pro.jpg"));
  
  
   $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
  
   $process5 = new Process('magick convert ^
  '.$path.'\image\Iphone-II-Pro.jpg ^
   -channel A -blur 0x8
   -compose hardlight
   '.$path.'\image\ms_light_map-phone1.png
    ');
  
    /* Makao sam komandu -separate proces 5   -colorspace gray -auto-level ^
   -blur 0x3 ^
   -contrast-stretch 0,50%% ^
   -depth 16 ^  -negate  -channel A -blur 0x8*/
  
  $process5->run();
  if (!$process5->isSuccessful()) {
   throw new ProcessFailedException($process5);
  }
   echo $process5->getOutput();
   echo '<img src="\image\ms_light_map-phone1.png">';
  
   $process6 = new Process('magick convert ^
   '.$path.'\design'. $imageName1. ' ^
   -channel matte -separate ^
   '.$path.'\image\ms_logo_displace_mask_phone1.png
    ');

   
  
  $process6->run();
  if (!$process6->isSuccessful()) {
   throw new ProcessFailedException($process6);
  }
   echo $process6->getOutput();
   echo '<img src="\image\ms_logo_displace_mask_phone1.png">';
  
   $process7 = new Process('magick convert ^
   '.$path.'\design'. $imageName1. ' ^
   '.$path.'\image\ms_light_map-phone1.png ^
   -geometry -250-150 ^
   -compose Multiply -composite ^
   '.$path.'\image\ms_logo_displace_mask_phone1.png ^
   -compose CopyOpacity -composite ^
   '.$path.'\image\ms_light_map_logo_phone1.png
   ');
  
  $process7->run();
  if (!$process7->isSuccessful()) {
  throw new ProcessFailedException($process7);
  }
  echo $process7->getOutput();
  echo '<img src="\image\ms_light_map_logo_phone1.png">';
  
  $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
  $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
  $src = new \Imagick(public_path("\image\ms_light_map_logo_phone1.png"));
  $src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 250, 150);
  $src2->writeImage(public_path("image/output1.png"));
   $process5 = new Process('magick  convert '.$path.'\image\output1.png -background "rgb(160,160,255)" -flatten  '.$path.'\image\out.png 
  ');
     $process5->run();
        if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
        }
         echo $process5->getOutput();
         echo '<img src="\image\out.png">'; 

  echo '<img src="data:image/jpg;base64,'.base64_encode($src2->getImageBlob()).'" alt="" />'; 
  /* $src1 = new \Imagick(public_path("\image\ms_light_map_logo_phone.png")); */
  
  
  $process8 = new Process('magick convert ^
  '.$path.'\image\ms_light_map-phone1.png ^
  '.$path.'\image\output1.png ^
  -compose ATop -composite ^
  
  -depth 16 ^
  '.$path.'\image\ms_product_phone1.png
  ');
  
  $process8->run();
  if (!$process8->isSuccessful()) {
  throw new ProcessFailedException($process8);
  }
  echo $process8->getOutput();
  echo '<img src="\image\ms_product_phone1.png">';
    dd();
    /*  
      $image = new Image();
      $image->newImage(1, 1, new ImagickPixel('#ffffff'));
      $image->setImageFormat('png');
      $pngData = $image->getImagesBlob();
      echo strpos($pngData, "\x89PNG\r\n\x1a\n") === 0 ? 'Ok' : 'Failed'; 
      */
 
     $file = $request->file('file');
      $imageName =  $file->getClientOriginalName();
      $filename = pathinfo($imageName, PATHINFO_FILENAME);
      
      $extension =  $request->file('file')->getClientOriginalExtension();
     // dd($extension);
      $image = $filename . "_" . time() . ".".$extension;
       $file->move('design/', $image); 
  
     /*   

try
{ */
        /** a file that does not exist **/
     /*   $image = 'C:\xampp\htdocs\www\imageMagick\public\design\3_1587218070.jpg'; */

    /*  $process = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\image\tshirt-16.jpg 
     -crop 400x600+400+750
      -colorspace gray 
     -blur 20x65000
      -auto-level 
      C:\xampp\htdocs\www\imageMagick\public\image\shirt_design_crop_b20_al.png');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\shirt_design_crop_b20_al.png">'; */
     echo '<img src="\image\tshirt-16.jpg">';

     $process = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\image\tshirt-16.jpg 
     -threshold 40%
      -negate 
      C:\xampp\htdocs\www\imageMagick\public\image\shirt_mask1.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\shirt_mask1.png">';


   /*    $im = new \Imagick(public_path('design/'. $image));
     $im->scaleImage(400,0);
     file_put_contents (public_path('design/'. $image), $im);   
     $imageName1 = "/" .  $image; */


    /* dd($im->getImageWidth(), $im->getImageHeight()); */
   /*   $process1 = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\design'  .$imageName1 . '
     C:\xampp\htdocs\www\imageMagick\public\image\shirt_design_crop_b20_al.png 
     -alpha set 
     -virtual-pixel transparent 
     -$compose displace
     -distort Perspective "0,0,0,0 400,0,300,0 0,514,20,400 400,514,300,450"
     -set option:$compose:args -5x-5 
     -composite
      C:\xampp\htdocs\www\imageMagick\public\image\shirt_displace_m5b1.png
     '); */

 /* 400, 514 */
    /*  -distort Perspective "-180,40,0,0 -200,1300,0,1300 1300,0,900,200 800,800,700,900" */

    $process1 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\image\Primjer-1080px-portrait.png
    
    -resize 800x450
    C:\xampp\htdocs\www\imageMagick\public\image\Primjer-1080px-portrait1.png
    '); 
    
 $process1->run();
  if (!$process1->isSuccessful()) {
      throw new ProcessFailedException($process1);    
} 

 echo '<img src="\image\picture_trans.png">';

    $process1 = new Process('magick convert   C:\xampp\htdocs\www\imageMagick\public\image\tshirt-16.jpg
    -alpha transparent  
     C:\xampp\htdocs\www\imageMagick\public\image\Primjer-1080px-portrait1.png
     -geometry +400+750 
     -composite 
     C:\xampp\htdocs\www\imageMagick\public\image\picture_trans.png
    '); 
    
 $process1->run();
  if (!$process1->isSuccessful()) {
      throw new ProcessFailedException($process1);    
} 

 echo '<img src="\image\picture_trans.png">';
 


 /* $process2 = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\image\tshirt-16.jpg
 C:\xampp\htdocs\www\imageMagick\public\image\shirt_displace_m5b1.png
  -geometry +400+750 
  
 -$compose over 
 -$compose hardlight
 -composite  C:\xampp\htdocs\www\imageMagick\public\image\shirt_product_displace_m5.png'); */

 $process2 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\image\picture_trans.png 
 -alpha extract 
 C:\xampp\htdocs\www\imageMagick\public\image\picture_mask.png');

 $process2->run();
 if (!$process2->isSuccessful()) {
     throw new ProcessFailedException($process2);    
} 

 echo '<img src="\image\picture_mask.png">';

  $process3 = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\image\tshirt-16.jpg  
 C:\xampp\htdocs\www\imageMagick\public\image\shirt_mask1.png
-alpha off
-compose copy_opacity
-composite
-scale 1x1!
-format "%[fx:mean]"
C:\xampp\htdocs\www\imageMagick\public\image\tshirt_process1.png
    
 ');
 
 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);    
}  
echo '<img src="\image\tshirt_process1.png">';




/* -format "%[fx:mean]" info: 0.206912 -$scale 1x1!  -composite  -$compose copy_opacity -alpha off */
$process4 = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\image\tshirt-16.jpg
C:\xampp\htdocs\www\imageMagick\public\image\shirt_mask1.png
 -alpha off 
 -compose copy_opacity
-composite 
-evaluate add 30% 
-alpha on -background "gray(50%)" 
-alpha background 
-alpha off
C:\xampp\htdocs\www\imageMagick\public\image\tshirt_process1.png');

$process4->run();
if (!$process4->isSuccessful()) {
   throw new ProcessFailedException($process4);    
} 
echo '<img src="\image\tshirt_process1.png">';


$process5 = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\image\tshirt_process1.png
C:\xampp\htdocs\www\imageMagick\public\image\lighting.png
 ');

$process5->run();
if (!$process5->isSuccessful()) {
   throw new ProcessFailedException($process5);    
} 
echo '<img src="\image\lighting.png">';



$process6 = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\image\tshirt_process1.png
-blur 0x4
C:\xampp\htdocs\www\imageMagick\public\image\displacement.png
 ');

$process6->run();
if (!$process6->isSuccessful()) {
   throw new ProcessFailedException($process6);    
} 
echo '<img src="\image\displacement.png">';


$process7 = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\image\picture_trans.png
C:\xampp\htdocs\www\imageMagick\public\image\lighting.png
-compose hardlight 
-composite C:\xampp\htdocs\www\imageMagick\public\image\picture_trans.png
-compose over 
-compose copy_opacity
-composite 
C:\xampp\htdocs\www\imageMagick\public\image\picture_light.png

 ');

$process7->run();
if (!$process7->isSuccessful()) {
   throw new ProcessFailedException($process7);    
} 
echo '<img src="\image\picture_light.png">';
/* 
convert picture_light.png displacement.png -define $compose:args=-20,-20 \
-$compose over -$compose displace -composite picture_light_displace.png
 */
$process8 = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\image\picture_light.png
C:\xampp\htdocs\www\imageMagick\public\image\displacement.png
-define $compose:args=-20,-20 
-compose over 
-compose displace 
-composite
C:\xampp\htdocs\www\imageMagick\public\image\picture_light_displace.png
 ');

$process8->run();
if (!$process8->isSuccessful()) {
   throw new ProcessFailedException($process8);    
} 
echo '<img src="\image\picture_light_displace.png">';

$process9 = new Process('magick convert C:\xampp\htdocs\www\imageMagick\public\image\tshirt-16.jpg
C:\xampp\htdocs\www\imageMagick\public\image\picture_light_displace.png
-compose over
 -composite C:\xampp\htdocs\www\imageMagick\public\image\t-shirt.png
 C:\xampp\htdocs\www\imageMagick\public\image\shirt_mask1.png 
-alpha off 
-compose copy_opacity
 -composite
C:\xampp\htdocs\www\imageMagick\public\image\shirt_picture.png 
 ');

$process9->run();
if (!$process9->isSuccessful()) {
   throw new ProcessFailedException($process9);    
} 
echo '<img src="\image\shirt_picture.png">';


/* convert shirt.jpg picture_light_displace.png -$compose over -composite \
shirt_mask.png -alpha off -$compose copy_opacity -composite shirt_picture.png */

dd();
/* convert picture_trans.png lighting.png -$compose hardlight -composite \
picture_mask.png -$compose over -$compose copy_opacity -composite picture_light.png
 */
    /*  convert shirt_design.jpg[192x144+90+105] -colorspace gray \
-blur 20x65000 -auto-level shirt_design_crop_b20_al.png */
      /*  $imagick2->adaptiveResizeImage(300,300);
       $imagick2->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);  */
        /** a new imagick object **/
        $imagick2 = new \Imagick(public_path('image/mockupTshirt.jpg'));
        $imagick1 = new \Imagick(public_path('image/mockupTshirt.jpg'));
         $imagick2->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT); 
         $imagick2->cropImage(300,300,170,170);
         $imagick2->setImageType(\Imagick::IMGTYPE_GRAYSCALE);
          $imagick2->blurImage(5,50); 
         echo '<img src="data:image/jpg;base64,'.base64_encode($imagick2->getImageBlob()).'" alt="" />';   
         
       
         $im = new \Imagick(public_path('design/'. $image));
      /*   $imagick2->scaleImage(500,0);
    $im = new \Imagick(public_path('design/'. $image));  */
  /*   $controlPoints = array( 10, 10, 
                        10, 5,

                        10, $im->getImageHeight() - 20,
                        10, $im->getImageHeight() - 5,

                        $im->getImageWidth() - 10, 10,
                        $im->getImageWidth() - 10, 20,

                        $im->getImageWidth() - 10, $im->getImageHeight() - 10,
                        $im->getImageWidth() - 10, $im->getImageHeight() - 30);

                        $im->distortImage(\Imagick::DISTORTION_PERSPECTIVE, $controlPoints, true); */
  //  $im->resizeImage(100, 100, \Imagick::FILTER_LANCZOS, 1, TRUE);
        $im->scaleImage(200,0);  
      
        $im->compositeImage($imagick2, \Imagick::COMPOSITE_DISPLACE, 20, 20);
        $im->setImageArtifact('$compose:args', "1,0,-0.5,0.5");  
        $imagick1->compositeImage($im, \Imagick::COMPOSITE_OVER      ,170,170);
   /*   $imagick2->compositeImage($im, \Imagick::COMPOSITE_DISPLACE        , 150, 240,);   */
  /*   $im->compositeImage($imagick2, \Imagick::COMPOSITE_MATHEMATICS     , 0, 0); */
   
   /*  $im->addImage($imagick2);
    $im->setImageFormat('png');
    $result = $im->mergeImageLayers(\Imagick::LAYERMETHOD_OPTIMIZE ); */



   // $im->setImageVirtualPixelMethod(1);
/* 		$points = array( 
			100,0, 80,120, # top left  
			$im->getImageWidth(),0, 300,120, # top right
			0,$im->getImageHeight(), 80,400, # bottom left 
			$im->getImageWidth(),$im->getImageHeight(), 300,390 # bottum right
		  );
		  
		  
      $im->distortImage( \Imagick::DISTORTION_PERSPECTIVE, $points, TRUE ); */
    //  $im->rotateImage(new \ImagickPixel(), 90);
       /* $im->adaptiveResizeImage(50,50);  */
     /*  $im->setImagePage($im->getImageWidth(), $im->getImageHeight(), 0, 0); */
     /*  $imagick2->addImage($im);
      $imagick2->setImageFormat('png');
  
      $result = $imagick2->mergeImageLayers(\Imagick::LAYERMETHOD_MERGE); */
     /*  $imagick2->setImageFormat('png');
      $imagick2->compositeImage($im, \Imagick::COMPOSITE_MATHEMATICS , 60, 120); */
      /* $im->setImageOrientation(\Imagick::ORIENTATION_RIGHTTOP);
      autoRotateImage($im); */
     /*  $im->roundCorners(5,3); */
     
/* 
     $imagick = new \Imagick(public_path('image/Primjer-1080px-portrait.png'));
     $imagick->scaleImage(200,0);
     $degrees = array(180, 45, 100, 20);
     $imagick->setimagebackgroundcolor("#fad888");
     $imagick->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_BACKGROUND);
     $imagick->distortImage(\Imagick::DISTORTION_ARC, $degrees, true);
     header("Content-Type: image/jpeg");
     echo $imagick; */

   /*   $a3 = new \Imagick(public_path('image/a3.png'));
     $a4 = new \Imagick(public_path('image/a4.png')); 
    $a1 = new \Imagick(public_path('image/Primjer-1080px-portrait.png'));
      $a1->scaleImage(1000,0); */
    /*   $a3->scaleImage(1000,0);
      $a4->scaleImage(1000,0);  */
    /*  $displaceMask = new \Imagick();
      $displaceMask->addImage($a3);
       $displaceMask->addImage($a4);
       $displaceMask->addImage($a4);  
      $displaceMask->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
      $displaceMask = $displaceMask->combineImages(\Imagick::CHANNEL_ALL);
      $displaceMask->compositeImage($a3, \Imagick::COMPOSITE_COPYRED, 0, 0);
      $displaceMask->compositeImage($a4, \Imagick::COMPOSITE_COPYGREEN, 0, 0);
      $displaceMask->compositeImage($a4, \Imagick::COMPOSITE_COPYBLUE, 0, 0);  

     $a1->setImageArtifact('$compose:args', '1600x83.669513037752'); */
   /*  $a1->compositeImage($displaceMask, \Imagick::COMPOSITE_DISPLACE, 100, 0); */
    /* $a1->trimImage(0); */
    /* $a1->compositeImage($displaceMask, \Imagick::COMPOSITE_DISSOLVE      ,0, 0); */
    $path = public_path();
    
     
     /* exec("/usr/local/bin/convert $path image/t-shirt.png -threshold 40% -negate $path image/shirt_mask1.png"); */
     /* exec("/usr/local/bin/convert rose: -resize 200x200 output.jpg"); */
   /*   echo '<img src=" output.jpg" alt="" />'; */
     /*  $displaceMask = new \Imagick();
      $displaceMask->newImage($a3->getImageWidth(), $a3->getImageHeight(), new \ImagickPixel('white'));
      $displaceMask->setImageFormat('png');
      $displaceMask->setImageColorspace(\Imagick::COLORSPACE_RGB);

      $displaceMask->compositeImage($a3, \Imagick::COMPOSITE_COPYRED, 0, 0);
      $displaceMask->compositeImage($a4, \Imagick::COMPOSITE_COPYGREEN, 0, 0);
      $displaceMask->compositeImage($a4, \Imagick::COMPOSITE_COPYBLUE, 0, 0);
  
      $image->setImageArtifact('$compose:args', '1600x83.669513037752');
      $image->compositeImage($displaceMask, \Imagick::COMPOSITE_DISPLACE, 0, 0); */

		 echo '<img src="data:image/jpg;base64,'.base64_encode($imagick1->getImageBlob()).'" alt="" />';  
/*}
 catch(Exception $e)
{
        echo $e->getMessage();
} */

     

   /*  $file = $request->file('file');
      $imageName =  $file->getClientOriginalName();
      $filename = pathinfo($imageName, PATHINFO_FILENAME);
      
      $extension =  $request->file('file')->getClientOriginalExtension();
     // dd($extension);
      $image = $filename . "_" . time() . ".".$extension;
      $file->move('design/', $image);


      /* $image1 = new Image();
      $image1 = Image::make(public_path('design/' . $image))->resize(300, 200);


      $image1->save('design/' . $image1->basename); 
      $image2 = new Image('design/' . $image1->basename); 
    
$image2->setImageCompressionQuality(30);*/
/* Activate matte 
$image1->setImageMatte(true);

      $points = array( 
        0,0, 80,120, # top left  
        $image1->width(),0, 300,10, # top right
        0,$image1->height(), 5,400, # bottom left 
        $image1->width(),$image1->height(), 380,390 # bottum right
      );
      
      
      $image1->distortImage( Image::DISTORTION_PERSPECTIVE, $points, TRUE );

      $image1->save('image/' . $image1->basename);  */

/*       $file = $request->file('file');
      $imageName =  $file->getClientOriginalName();
      $filename = pathinfo($imageName, PATHINFO_FILENAME);
      
      $extension =  $request->file('file')->getClientOriginalExtension();
     // dd($extension);
      $image = $filename . "_" . time() . ".".$extension;
      $file->move('design/', $image);

      $image1 = Image::make(public_path('design/' . $image));
  

     
      $points = array( 
                    0,0, 80,120, # top left  
                    $image1->width(),0, 300,10, # top right
                    0,$image1->height(), 5,400, # bottom left 
                    $image1->width(),$image1->height(), 380,390 # bottum right
                  );
      
      $image1->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
      $image1->distortImage( Imagick::DISTORTION_PERSPECTIVE, $points, TRUE );
      
      header( "Content-Type: image/jpeg" ); 
     
     */
    
  
   /*  $controlPoints = array( 10, 10, 
                        10, 5,

                        10, $image1->getImageHeight() - 20,
                        10, $image1->getImageHeight() - 5,

                        $image1->getImageWidth() - 10, 10,
                        $image1->getImageWidth() - 10, 20,

                        $image1->getImageWidth() - 10, $image1->getImageHeight() - 10,
                        $image1->getImageWidth() - 10, $image1->getImageHeight() - 30);

                        $image1->distortImage(Imagick::DISTORTION_PERSPECTIVE, $controlPoints, true);


      $image1->save('design/'.$image1->basename);
 */
   /*  $image = new Imagick();
    $image->newImage(1, 1, new ImagickPixel('#ffffff'));
    $image->setImageFormat('png');
    $pngData = $image->getImagesBlob();
    echo strpos($pngData, "\x89PNG\r\n\x1a\n") === 0 ? 'Ok' : 'Failed';  */
    }
    

    //This function prints a text array as an html list.


  public function check(){

    if (!extension_loaded('imagick')){
    echo 'imagick not installed';
    }else{
      echo 'aa';
        }
    dd();
  //Try to get ImageMagick "convert" program version number.
  exec("convert -version", $out, $rcode);
  //Print the return code: 0 if OK, nonzero if error. 
  echo "Version return code is $rcode <br>"; 
  //Print the output of "convert -version"    
   
  
  $alist = "<ul>";
  for ($i = 0; $i < sizeof($out); $i++) {
    $alist .= "<li>$out[$i]";
  }
  $alist .= "</ul>";
  echo $alist;
}



}
