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

       $path = public_path();

      /*  $process0 = new Process(' convert -version
        ');
        $process0->run();
        if (!$process0->isSuccessful()) {
            throw new ProcessFailedException($process0);
        } 
        echo $process0->getOutput();

        dd(); */
       $process0 = new Process('magick convert '.$path.'\image\U-one-16.jpg 
       -resize 1500x2500
       '.$path.'\image\U-one-16.jpg 
        ');
        $process0->run();
        if (!$process0->isSuccessful()) {
            throw new ProcessFailedException($process0);
        } 

        $process = new Process('magick convert   '.$path.'\image\U-one-16.jpg[303x322+610+531] 
       -colorspace gray 
       -blur 10x250 
       -auto-level
       '.$path.'\image\displace_map.png
        ');
  
   $process->run();
   if (!$process->isSuccessful()) {
       throw new ProcessFailedException($process);
   }
       echo $process->getOutput();
       echo '<img src="\image\displace_map.png">'; 

       $imageName1 = "/" .  $image; 

       $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
       -resize 300x300
       '.$path.'\design' . $imageName1 . '
       '); 
       
    $process1->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process1);    
   } 


       $process2 = new Process('magick convert 
       '.$path.'\design' . $imageName1 . '
       -bordercolor transparent -border 12x12 -thumbnail 303x322 
       '.$path.'\image\ms_temp.png
        ');
  
   $process2->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process2);
   }
       echo $process2->getOutput();
       echo '<img src="\image\ms_temp.png">';


       list($width, $height) = getimagesize($path.'\image\ms_temp.png');

     
       $X = 610 + (303-$width)/2;
       $Y = 531 +  (322-$height)/2;
      

        $process3 = new Process('magick convert 
       '.$path.'\image\U-one-16.jpg[303x322+610+531] 
       -colorspace gray -blur 10x250 -auto-level 
       -depth 16 
       '.$path.'\image\ms_displace_map_girl_white_regular.png
        ');
  
   $process3->run();
   if (!$process3->isSuccessful()) {
       throw new ProcessFailedException($process3);
   }
       echo $process3->getOutput();
       echo '<img src="\image\ms_displace_map_girl_white_regular.png">'; 
      
       $process4 = new Process('magick convert ^
       '.$path.'\image\ms_temp.png ^
       '.$path.'\image\ms_displace_map_girl_white_regular.png ^
       -alpha set -virtual-pixel transparent ^
       -compose displace -set option:compose:args -5x-5 -composite ^
       -depth 16 ^
       '.$path.'\image\ms_displaced_logo.png
     
        ');
  
   $process4->run();
   if (!$process4->isSuccessful()) {
       throw new ProcessFailedException($process4);
   }
       echo $process4->getOutput();
       echo '<img src="\image\ms_displaced_logo.png">';

       
        $process5 = new Process('magick convert ^
       '.$path.'\image\U-one-16.jpg[303x322+610+531] ^
       -colorspace gray -auto-level ^
       -blur 0x4 ^
       -contrast-stretch 0,30%% ^
       -depth 16 ^
       '.$path.'\image\ms_light_map_girl_white_regular.png
        ');

/*         Makao sam komandu -separate proces 5 */
  
   $process5->run();
   if (!$process5->isSuccessful()) {
       throw new ProcessFailedException($process5);
   }
       echo $process5->getOutput();
       echo '<img src="\image\ms_light_map_girl_white_regular.png">'; 
       
       $process6 = new Process('magick convert ^
       '.$path.'\image\ms_displaced_logo.png ^
       -channel matte -separate ^
       '.$path.'\image\ms_logo_displace_mask.png
        ');
  
   $process6->run();
   if (!$process6->isSuccessful()) {
       throw new ProcessFailedException($process6);
   }
       echo $process6->getOutput();
       echo '<img src="\image\ms_logo_displace_mask.png">';
       
       $process7 = new Process('magick convert ^
       '.$path.'\image\ms_displaced_logo.png ^
       '.$path.'\image\ms_light_map_girl_white_regular.png ^
       -compose Multiply -composite ^
       '.$path.'\image\ms_logo_displace_mask.png ^
       -compose CopyOpacity -composite ^
       '.$path.'\image\ms_light_map_logo.png
       ');
 
  $process7->run();
  if (!$process7->isSuccessful()) {
      throw new ProcessFailedException($process7);
  }
      echo $process7->getOutput();
      echo '<img src="\image\ms_light_map_logo.png">';
      
     
     /*  -geometry +984+1101 */

       $process8 = new Process('magick convert ^
       '.$path.'\image\U-one-16.jpg ^
       '.$path.'\image\ms_light_map_logo.png ^
       -geometry +'.$X.'+'.$Y.'
       -compose over -composite ^
       -depth 16 ^
       '.$path.'\image\ms_product.png
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

        $process0 = new Process('magick convert  C:\xampp\htdocs\www\imageMagick\public\image\U-one-13.jpg 
       -resize 1500x2500
       C:\xampp\htdocs\www\imageMagick\public\image\U-one-13.jpg 
        ');
        $process0->run();
        if (!$process0->isSuccessful()) {
            throw new ProcessFailedException($process0);
        } 

        $path = public_path();

   /*     $process = new Process('magick convert   '.$path.'\image\U-one-13.jpg[403x422+540+561] 
       -colorspace gray 
       -blur 10x250 
       -auto-level
       '.$path.'\image\displace_map.png
        ');
  
   $process->run();
   if (!$process->isSuccessful()) {
       throw new ProcessFailedException($process);
   }
       echo $process->getOutput();
       echo '<img src="\image\displace_map.png">'; */

       $imageName1 = "/" .  $image; 

       $process1 = new Process('magick convert  '.$path.'\design' . $imageName1 . '
       -resize 300x300
       '.$path.'\design' . $imageName1 . '
       '); 
       
    $process1->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process1);    
   } 


       $process2 = new Process('magick convert 
       '.$path.'\design' . $imageName1 . '
       -bordercolor transparent -border 12x12 -thumbnail 303x322 
       '.$path.'\image\ms_temp.png
        ');
  
   $process2->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process2);
   }
       echo $process2->getOutput();
       echo '<img src="\image\ms_temp.png">';

      

       $process3 = new Process('magick convert 
       '.$path.'\image\U-one-13.jpg[303x322+599+561] 
       -colorspace gray -blur 10x250 -auto-level 
       -depth 16 
       '.$path.'\image\ms_displace_map_girl_navy_regular.png
        ');
  
   $process3->run();
   if (!$process3->isSuccessful()) {
       throw new ProcessFailedException($process3);
   }
       echo $process3->getOutput();
       echo '<img src="\image\ms_displace_map_girl_navy_regular.png">'; 
      
       $process4 = new Process('magick convert ^
       '.$path.'\image\ms_temp.png ^
       '.$path.'\image\ms_displace_map_girl_navy_regular.png ^
       -alpha set -virtual-pixel transparent ^
       -compose displace -set option:compose:args -5x-5 -composite ^
       -depth 16 ^
       '.$path.'\image\ms_displaced_logo.png
     
        ');
  
   $process4->run();
   if (!$process4->isSuccessful()) {
       throw new ProcessFailedException($process4);
   }
       echo $process4->getOutput();
       echo '<img src="\image\ms_displaced_logo.png">';

       
        $process5 = new Process('magick convert ^
       '.$path.'\image\U-one-13.jpg[303x322+599+561] ^
       -colorspace gray -auto-level ^
       -blur 0x3 ^
       -contrast-stretch 0,50%% ^
       -depth 26 ^
       '.$path.'\image\ms_light_map_girl_navy_regular.png
        ');

      
  
   $process5->run();
   if (!$process5->isSuccessful()) {
       throw new ProcessFailedException($process5);
   }
       echo $process5->getOutput();
       echo '<img src="\image\ms_light_map_girl_navy_regular.png">'; 

   
       $process6 = new Process('magick convert ^
       '.$path.'\image\ms_displaced_logo.png ^
       -channel matte -separate ^
       '.$path.'\image\ms_logo_displace_mask.png
        ');
  
   $process6->run();
   if (!$process6->isSuccessful()) {
       throw new ProcessFailedException($process6);
   }
       echo $process6->getOutput();
       echo '<img src="\image\ms_logo_displace_mask.png">';
       
       $process7 = new Process('magick convert ^
       '.$path.'\image\ms_displaced_logo.png ^
       '.$path.'\image\ms_light_map_girl_navy_regular.png ^
       -compose Multiply -composite ^
       '.$path.'\image\ms_logo_displace_mask.png ^
       -compose CopyOpacity -composite ^
       '.$path.'\image\ms_light_map_logo.png
       ');
 
  $process7->run();
  if (!$process7->isSuccessful()) {
      throw new ProcessFailedException($process7);
  }
      echo $process7->getOutput();
      echo '<img src="\image\ms_light_map_logo.png">';
      


          
    list($width, $height) = getimagesize($path.'\image\ms_light_map_logo.png');

      
    $X = 599 + (303-$width)/2;
    $Y = 561 +  (322-$height)/2;

       $process8 = new Process('magick convert ^
       '.$path.'\image\U-one-13.jpg ^
       '.$path.'\image\ms_light_map_logo.png ^
       -geometry +'.$X.'+'.$Y.' ^
       -compose over -composite ^
       -depth 16 ^
       '.$path.'\image\ms_product.png
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

       $path = public_path();

       $process0 = new Process('magick convert  '.$path.'\image\U-one-26.jpg 
       -resize 1500x2500
       '.$path.'\image\U-one-26.jpg 
        ');
        $process0->run();
        if (!$process0->isSuccessful()) {
            throw new ProcessFailedException($process0);
        }

   /*     $process = new Process('magick convert   '.$path.'\image\U-one-26.jpg[403x422+584+601] 
       -colorspace gray 
       -blur 10x250 
       -auto-level
       '.$path.'\image\displace_map.png
        ');
  
   $process->run();
   if (!$process->isSuccessful()) {
       throw new ProcessFailedException($process);
   }
       echo $process->getOutput();
       echo '<img src="\image\displace_map.png">'; */

       $imageName1 = "/" .  $image; 

       $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
    
       -resize 300x300
       '.$path.'\design' . $imageName1 . '
       '); 
       
    $process1->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process1);    
   } 


       $process2 = new Process('magick convert 
       '.$path.'\design' . $imageName1 . '
       -bordercolor transparent -border 12x12 -thumbnail 403x422 
       '.$path.'\image\ms_temp.png
        ');
  
   $process2->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process2);
   }
       echo $process2->getOutput();
       echo '<img src="\image\ms_temp.png">';

      
 
    /*    $process3 = new Process('magick convert 
       '.$path.'\image\U-one-26.jpg[403x422+560+701] 
       -colorspace gray -blur 10x250 -auto-level 
       -depth 16 
       '.$path.'\image\ms_displace_map_girl_red_polo.png
        ');
  
   $process3->run();
   if (!$process3->isSuccessful()) {
       throw new ProcessFailedException($process3);
   }
       echo $process3->getOutput();
       echo '<img src="\image\ms_displace_map_girl_red_polo.png">';  */
      
       $process4 = new Process('magick convert ^
       '.$path.'\image\ms_temp.png ^
       '.$path.'\image\ms_displace_map_girl_red_polo.png ^
       -alpha set -virtual-pixel transparent ^
       -compose displace -set option:compose:args -5x-5 -composite ^
       -depth 16 ^
       '.$path.'\image\ms_displaced_logo.png
     
        ');
  
   $process4->run();
   if (!$process4->isSuccessful()) {
       throw new ProcessFailedException($process4);
   }
       echo $process4->getOutput();
       echo '<img src="\image\ms_displaced_logo.png">';

       
  /*      $process5 = new Process('magick convert ^
       '.$path.'\image\U-one-26.jpg[403x422+560+701] ^
       -colorspace gray -auto-level ^
       -blur 0x3 ^
       -contrast-stretch 0,30%% ^
       -depth 16 ^
       '.$path.'\image\ms_light_map_girl_red_polo.png
        ');

           Makao sam komandu -separate proces 5  
   $process5->run();
   if (!$process5->isSuccessful()) {
       throw new ProcessFailedException($process5);
   }
       echo $process5->getOutput();
       echo '<img src="\image\ms_light_map_girl_red_polo.png">';   */
  
       
       $process6 = new Process('magick convert ^
       '.$path.'\image\ms_displaced_logo.png ^
       -channel matte -separate ^
       '.$path.'\image\ms_logo_displace_mask.png
        ');
  
   $process6->run();
   if (!$process6->isSuccessful()) {
       throw new ProcessFailedException($process6);
   }
       echo $process6->getOutput();
       echo '<img src="\image\ms_logo_displace_mask.png">';
       
       $process7 = new Process('magick convert ^
       '.$path.'\image\ms_displaced_logo.png ^
       '.$path.'\image\ms_light_map_girl_red_polo.png ^
       -compose Multiply -composite ^
       '.$path.'\image\ms_logo_displace_mask.png ^
       -compose CopyOpacity -composite ^
       '.$path.'\image\ms_light_map_logo.png
       ');
 
  $process7->run();
  if (!$process7->isSuccessful()) {
      throw new ProcessFailedException($process7);
  }
      echo $process7->getOutput();
      echo '<img src="\image\ms_light_map_logo.png">';
      

      list($width, $height) = getimagesize($path.'\image\ms_light_map_logo.png');

      $X = 560 + (403-$width)/2;
      $Y = 701 +  (422-$height)/2;
          
      

       $process8 = new Process('magick convert ^
       '.$path.'\image\U-one-26.jpg ^
       '.$path.'\image\ms_light_map_logo.png ^
       -geometry +'.$X.'+'.$Y.' ^
       -compose over -composite ^
       -depth 16 ^
       '.$path.'\image\ms_product.png
       ');
 
  $process8->run();
  if (!$process8->isSuccessful()) {
      throw new ProcessFailedException($process8);
  }
      echo $process8->getOutput();
      echo '<img src="\image\ms_product.png">';
      
      
     /*  echo '<img src="data:image/jpg;base64,'.base64_encode($a1->getImageBlob()).'" alt="" />';   */
    }

    public function uploadMockup6(Request $request){
        $file = $request->file('file');
        $imageName =  $file->getClientOriginalName();
        $imageName = preg_replace('/\s+/', '', $imageName);
        $filename = pathinfo($imageName, PATHINFO_FILENAME);
        
        $extension =  $request->file('file')->getClientOriginalExtension();
        $image = $filename . "_" . time() . ".".$extension;
         $file->move('design/', $image); 
  
         $path = public_path();
  
         $process0 = new Process('magick convert  '.$path.'\image\U-one-6.jpg 
         -resize 1500x2500
         '.$path.'\image\U-one-6.jpg 
          ');
          $process0->run();
          if (!$process0->isSuccessful()) {
              throw new ProcessFailedException($process0);
          }
  
          $process = new Process('magick convert   '.$path.'\image\U-one-6.jpg[303x322+620+571] 
         -colorspace gray 
         -blur 10x250 
         -auto-level
         '.$path.'\image\displace_map_girl_red_regular.png
          ');
    
     $process->run();
     if (!$process->isSuccessful()) {
         throw new ProcessFailedException($process);
     }
         echo $process->getOutput();
         echo '<img src="\image\displace_map_girl_red_regular.png">'; 
  
         $imageName1 = "/" .  $image; 
  
         $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
      
         -resize 300x300
         '.$path.'\design' . $imageName1 . '
         '); 
         
      $process1->run();
       if (!$process1->isSuccessful()) {
           throw new ProcessFailedException($process1);    
     } 
  
  
         $process2 = new Process('magick convert 
         '.$path.'\design' . $imageName1 . '
         -bordercolor transparent -border 12x12 -thumbnail 303x322 
         '.$path.'\image\ms_temp.png
          ');
    
     $process2->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process2);
     }
         echo $process2->getOutput();
         echo '<img src="\image\ms_temp.png">';
  
        
  
          $process3 = new Process('magick convert 
         '.$path.'\image\U-one-6.jpg[303x322+620+571] 
         -colorspace gray -blur 10x250 -auto-level 
         -depth 16 
         '.$path.'\image\ms_displace_map_girl_red_regular.png
          ');
    
     $process3->run();
     if (!$process3->isSuccessful()) {
         throw new ProcessFailedException($process3);
     }
         echo $process3->getOutput();
         echo '<img src="\image\ms_displace_map_girl_red_regular.png">'; 
        
         $process4 = new Process('magick convert ^
         '.$path.'\image\ms_temp.png ^
         '.$path.'\image\ms_displace_map_girl_red_regular.png ^
         -alpha set -virtual-pixel transparent ^
         -compose displace -set option:compose:args -5x-5 -composite ^
         -depth 16 ^
         '.$path.'\image\ms_displaced_logo_girl_red_regular.png
       
          ');
    
     $process4->run();
     if (!$process4->isSuccessful()) {
         throw new ProcessFailedException($process4);
     }
         echo $process4->getOutput();
         echo '<img src="\image\ms_displaced_logo_girl_red_regular.png">';
  
         
          $process5 = new Process('magick convert ^
         '.$path.'\image\U-one-6.jpg[303x322+620+571] ^
         -colorspace gray -auto-level ^
         -blur 0x3 ^
         -contrast-stretch 0,30%% ^
         -depth 16 ^
         '.$path.'\image\ms_light_map_girl_red_regular.png
          ');
  
          /*  Makao sam komandu -separate proces 5  */
    
     $process5->run();
     if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
     }
         echo $process5->getOutput();
         echo '<img src="\image\ms_light_map_girl_red_regular.png">'; 
         
         $process6 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo_girl_red_regular.png ^
         -channel matte -separate ^
         '.$path.'\image\ms_logo_displace_mask.png
          ');
    
     $process6->run();
     if (!$process6->isSuccessful()) {
         throw new ProcessFailedException($process6);
     }
         echo $process6->getOutput();
         echo '<img src="\image\ms_logo_displace_mask.png">';
         
         $process7 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo_girl_red_regular.png ^
         '.$path.'\image\ms_light_map_girl_red_regular.png ^
         -compose Multiply -composite ^
         '.$path.'\image\ms_logo_displace_mask.png ^
         -compose CopyOpacity -composite ^
         '.$path.'\image\ms_light_map_logo.png
         ');
   
    $process7->run();
    if (!$process7->isSuccessful()) {
        throw new ProcessFailedException($process7);
    }
        echo $process7->getOutput();
        echo '<img src="\image\ms_light_map_logo.png">';
        
  
        list($width, $height) = getimagesize($path.'\image\ms_light_map_logo.png');
        
    $X = 620 + (303-$width)/2;
    $Y = 571 +  (322-$height)/2;
        
  
         $process8 = new Process('magick convert ^
         '.$path.'\image\U-one-6.jpg ^
         '.$path.'\image\ms_light_map_logo.png ^
         -geometry +'.$X.'+'.$Y.' ^
         -compose over -composite ^
         -depth 16 ^
         '.$path.'\image\ms_product.png
         ');
   
    $process8->run();
    if (!$process8->isSuccessful()) {
        throw new ProcessFailedException($process8);
    }
        echo $process8->getOutput();
        echo '<img src="\image\ms_product.png">';
        
        
       /*  echo '<img src="data:image/jpg;base64,'.base64_encode($a1->getImageBlob()).'" alt="" />';   */
      }

      public function uploadMockup7(Request $request){
        $file = $request->file('file');
        $imageName =  $file->getClientOriginalName();
        $imageName = preg_replace('/\s+/', '', $imageName);
        $filename = pathinfo($imageName, PATHINFO_FILENAME);
        
        $extension =  $request->file('file')->getClientOriginalExtension();
        $image = $filename . "_" . time() . ".".$extension;
         $file->move('design/', $image); 
  
         $path = public_path();
  
         $process0 = new Process('magick convert  '.$path.'\image\U-one-5.jpg 
         -resize 1500x2500
         '.$path.'\image\U-one-5.jpg 
          ');
          $process0->run();
          if (!$process0->isSuccessful()) {
              throw new ProcessFailedException($process0);
          }
  
         /*  $process = new Process('magick convert   '.$path.'\image\U-one-5.jpg[403x422+545+601] 
         -colorspace gray 
         -blur 10x250 
         -auto-level
         '.$path.'\image\displace_map_girl_black_regular.png
          ');
    
     $process->run();
     if (!$process->isSuccessful()) {
         throw new ProcessFailedException($process);
     }
         echo $process->getOutput(); */
        
  
         $imageName1 = "/" .  $image; 
  
         $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
         -resize 300x300
         '.$path.'\design' . $imageName1 . '
         '); 
         
      $process1->run();
       if (!$process1->isSuccessful()) {
           throw new ProcessFailedException($process1);    
     } 
  
  
         $process2 = new Process('magick convert 
         '.$path.'\design' . $imageName1 . '
         -bordercolor transparent -border 12x12 -thumbnail 303x322 
         '.$path.'\image\ms_temp.png
          ');
    
     $process2->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process2);
     }
         echo $process2->getOutput();
         echo '<img src="\image\ms_temp.png">';
  
        
  
           $process3 = new Process('magick convert 
         '.$path.'\image\U-one-5.jpg[303x322+595+531] 
         -colorspace gray -blur 10x250 -auto-level 
         -depth 60 
         '.$path.'\image\ms_displace_map_girl_black_regular.png
          ');
    
     $process3->run();
     if (!$process3->isSuccessful()) {
         throw new ProcessFailedException($process3);
     }
         echo $process3->getOutput();
         echo '<img src="\image\ms_displace_map_girl_black_regular.png">';  
        
         $process4 = new Process('magick convert ^
         '.$path.'\image\ms_temp.png ^
         '.$path.'\image\ms_displace_map_girl_black_regular.png ^
         -alpha set -virtual-pixel transparent ^
         -compose displace -set option:compose:args -5x-5 -composite ^
         -depth 16 ^
         '.$path.'\image\ms_displaced_logo_girl_black_regular.png
       
          ');
    
     $process4->run();
     if (!$process4->isSuccessful()) {
         throw new ProcessFailedException($process4);
     }
         echo $process4->getOutput();
         echo '<img src="\image\ms_displaced_logo_girl_black_regular.png">';
  
             
        $process5 = new Process('magick convert ^
         '.$path.'\image\U-one-5.jpg[303x322+595+531] ^
         -colorspace gray -auto-level ^
         -blur 0x5 ^
         -contrast-stretch 0,50%% ^
         -depth 16 ^
         '.$path.'\image\ms_light_map_girl_black_regular.png
          ');
  
       /*   Makao sam komandu -separate proces 5 */
    
     $process5->run();
     if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
     }
         echo $process5->getOutput();
         echo '<img src="\image\ms_light_map_girl_black_regular.png">'; 
          
         
         $process6 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo_girl_black_regular.png ^
         -channel matte -separate ^
         '.$path.'\image\ms_logo_displace_mask.png
          ');
    
     $process6->run();
     if (!$process6->isSuccessful()) {
         throw new ProcessFailedException($process6);
     }
         echo $process6->getOutput();
         echo '<img src="\image\ms_logo_displace_mask.png">';
         
         $process7 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo_girl_black_regular.png ^
         '.$path.'\image\ms_light_map_girl_black_regular.png ^
         -compose Multiply -composite ^
         '.$path.'\image\ms_logo_displace_mask.png ^
         -compose CopyOpacity -composite ^
         '.$path.'\image\ms_light_map_logo.png
         ');
   
    $process7->run();
    if (!$process7->isSuccessful()) {
        throw new ProcessFailedException($process7);
    }
        echo $process7->getOutput();
        echo '<img src="\image\ms_light_map_logo.png">';
        
  
        list($width, $height) = getimagesize($path.'\image\ms_light_map_logo.png');
        
    $X = 595 + (303-$width)/2;
    $Y = 531 +  (322-$height)/2;
        
  
         $process8 = new Process('magick convert ^
         '.$path.'\image\U-one-5.jpg ^
         '.$path.'\image\ms_light_map_logo.png ^
         -geometry +'.$X.'+'.$Y.' ^
         -compose over -composite ^
         -depth 16 ^
         '.$path.'\image\ms_product.png
         ');
   
    $process8->run();
    if (!$process8->isSuccessful()) {
        throw new ProcessFailedException($process8);
    }
        echo $process8->getOutput();
        echo '<img src="\image\ms_product.png">';
        
        
       /*  echo '<img src="data:image/jpg;base64,'.base64_encode($a1->getImageBlob()).'" alt="" />';   */
      }


      public function uploadMockup8(Request $request){
        $file = $request->file('file');
        $imageName =  $file->getClientOriginalName();
        $imageName = preg_replace('/\s+/', '', $imageName);
        $filename = pathinfo($imageName, PATHINFO_FILENAME);
        
        $extension =  $request->file('file')->getClientOriginalExtension();
        $image = $filename . "_" . time() . ".".$extension;
         $file->move('design/', $image); 
  
         $path = public_path();
  
         $process0 = new Process('magick convert  '.$path.'\image\U-one-24.jpg 
         -resize 2000x3000
         '.$path.'\image\U-one-24.jpg 
          ');
          $process0->run();
          if (!$process0->isSuccessful()) {
              throw new ProcessFailedException($process0);
          }
  
    /*      $process = new Process('magick convert   '.$path.'\image\U-one-6.jpg[403x422+835+901] 
         -colorspace gray 
         -blur 10x250 
         -auto-level
         '.$path.'\image\displace_map_girl_red_regular.png
          ');
    
     $process->run();
     if (!$process->isSuccessful()) {
         throw new ProcessFailedException($process);
     }
         echo $process->getOutput();
         echo '<img src="\image\displace_map.png">'; */
  
         $imageName1 = "/" .  $image; 
  
         $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
         -resize 400x400
         '.$path.'\design' . $imageName1 . '
         '); 
         
      $process1->run();
       if (!$process1->isSuccessful()) {
           throw new ProcessFailedException($process1);    
     } 
  
  
         $process2 = new Process('magick convert 
         '.$path.'\design' . $imageName1 . '
         -bordercolor transparent -border 12x12 -thumbnail 403x422 
         '.$path.'\image\ms_temp.png
          ');
    
     $process2->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process2);
     }
         echo $process2->getOutput();
         echo '<img src="\image\ms_temp.png">';
  
        
  
        /*    $process3 = new Process('magick convert 
         '.$path.'\image\U-one-24.jpg[403x422+820+850] 
         -colorspace gray -blur 10x250 -auto-level 
         -depth 16 
         '.$path.'\image\ms_displace_map_girl_white_polo.png
          ');
    
     $process3->run();
     if (!$process3->isSuccessful()) {
         throw new ProcessFailedException($process3);
     }
         echo $process3->getOutput();
         echo '<img src="\image\ms_displace_map_girl_white_polo.png">';   */
        
         $process4 = new Process('magick convert ^
         '.$path.'\image\ms_temp.png ^
         '.$path.'\image\ms_displace_map_girl_white_polo.png ^
         -alpha set -virtual-pixel transparent ^
         -compose displace -set option:compose:args -5x-5 -composite ^
         -depth 16 ^
         '.$path.'\image\ms_displaced_logo.png
       
          ');
    
     $process4->run();
     if (!$process4->isSuccessful()) {
         throw new ProcessFailedException($process4);
     }
         echo $process4->getOutput();
         echo '<img src="\image\ms_displaced_logo.png">';
  
    /*            
        $process5 = new Process('magick convert ^
         '.$path.'\image\U-one-24.jpg[403x422+820+850] ^
         -colorspace gray -auto-level ^
         -blur 0x5 ^
         -contrast-stretch 0,50%% ^
         -depth 16 ^
         '.$path.'\image\ms_light_map_girl_white_polo.png
          ');
  
       Makao sam komandu -separate proces 5 
    
     $process5->run();
     if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
     }
         echo $process5->getOutput();
         echo '<img src="\image\ms_light_map_girl_white_polo.png">'; */
          
         
         $process6 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         -channel matte -separate ^
         '.$path.'\image\ms_logo_displace_mask.png
          ');
    
     $process6->run();
     if (!$process6->isSuccessful()) {
         throw new ProcessFailedException($process6);
     }
         echo $process6->getOutput();
         echo '<img src="\image\ms_logo_displace_mask.png">';
         
         $process7 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         '.$path.'\image\ms_light_map_girl_white_polo.png ^
         -compose Multiply -composite ^
         '.$path.'\image\ms_logo_displace_mask.png ^
         -compose CopyOpacity -composite ^
         '.$path.'\image\ms_light_map_logo.png
         ');
   
    $process7->run();
    if (!$process7->isSuccessful()) {
        throw new ProcessFailedException($process7);
    }
        echo $process7->getOutput();
        echo '<img src="\image\ms_light_map_logo.png">';
        
  
        list($width, $height) = getimagesize($path.'\image\ms_light_map_logo.png');
    $X = 820 + (403-$width)/2;
    $Y = 850 +  (422-$height)/2;
        
  
         $process8 = new Process('magick convert ^
         '.$path.'\image\U-one-24.jpg ^
         '.$path.'\image\ms_light_map_logo.png ^
         -geometry +'.$X.'+'.$Y.' ^
         -compose over -composite ^
         -depth 16 ^
         '.$path.'\image\ms_product.png
         ');
   
    $process8->run();
    if (!$process8->isSuccessful()) {
        throw new ProcessFailedException($process8);
    }
        echo $process8->getOutput();
        echo '<img src="\image\ms_product.png">';
        
        
       /*  echo '<img src="data:image/jpg;base64,'.base64_encode($a1->getImageBlob()).'" alt="" />';   */
      }

      public function uploadMockup9(Request $request){
        $file = $request->file('file');
        $imageName =  $file->getClientOriginalName();
        $imageName = preg_replace('/\s+/', '', $imageName);
        $filename = pathinfo($imageName, PATHINFO_FILENAME);
        
        $extension =  $request->file('file')->getClientOriginalExtension();
        $image = $filename . "_" . time() . ".".$extension;
         $file->move('design/', $image); 
  
         $path = public_path();
  
        /*  $process0 = new Process('magick convert  '.$path.'\image\U-one-22.jpg 
         -resize 2000x3000
         '.$path.'\image\U-one-22.jpg 
          ');
          $process0->run();
          if (!$process0->isSuccessful()) {
              throw new ProcessFailedException($process0);
          } */
  
    /*      $process = new Process('magick convert   '.$path.'\image\U-one-6.jpg[403x422+835+901] 
         -colorspace gray 
         -blur 10x250 
         -auto-level
         '.$path.'\image\displace_map_girl_red_regular.png
          ');
    
     $process->run();
     if (!$process->isSuccessful()) {
         throw new ProcessFailedException($process);
     }
         echo $process->getOutput();
         echo '<img src="\image\displace_map.png">'; */
  
         $imageName1 = "/" .  $image; 
  
         $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
         -resize 400x400
         '.$path.'\design' . $imageName1 . '
         '); 
         
      $process1->run();
       if (!$process1->isSuccessful()) {
           throw new ProcessFailedException($process1);    
     } 
  
  
         $process2 = new Process('magick convert 
         '.$path.'\design' . $imageName1 . '
         -bordercolor transparent -border 12x12 -thumbnail 403x422 
         '.$path.'\image\ms_temp.png
          ');
    
     $process2->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process2);
     }
         echo $process2->getOutput();
         echo '<img src="\image\ms_temp.png">';
  
        
  
      /*      $process3 = new Process('magick convert 
         '.$path.'\image\U-one-22.jpg[403x422+790+930] 
         -colorspace gray -blur 10x250 -auto-level 
         -depth 16 
         '.$path.'\image\ms_displace_map_girl_black_polo.png
          ');
    
     $process3->run();
     if (!$process3->isSuccessful()) {
         throw new ProcessFailedException($process3);
     }
         echo $process3->getOutput();
         echo '<img src="\image\ms_displace_map_girl_black_polo.png">';  */  
        
         $process4 = new Process('magick convert ^
         '.$path.'\image\ms_temp.png ^
         '.$path.'\image\ms_displace_map_girl_black_polo.png ^
         -alpha set -virtual-pixel transparent ^
         -compose displace -set option:compose:args -5x-5 -composite ^
         -depth 16 ^
         '.$path.'\image\ms_displaced_logo.png
       
          ');
    
     $process4->run();
     if (!$process4->isSuccessful()) {
         throw new ProcessFailedException($process4);
     }
         echo $process4->getOutput();
         echo '<img src="\image\ms_displaced_logo.png">';
  
                
    /*     $process5 = new Process('magick convert ^
         '.$path.'\image\U-one-22.jpg[403x422+790+930] ^
         -colorspace gray -auto-level ^
         -blur 0x5 ^
         -contrast-stretch 0,50%% ^
         -depth 16 ^
         '.$path.'\image\ms_light_map_girl_black_polo.png
          ');
  
       Makao sam komandu -separate proces 5  
    
     $process5->run();
     if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
     }
         echo $process5->getOutput();
         echo '<img src="\image\ms_light_map_girl_black_polo.png">';  */
          
         
         $process6 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         -channel matte -separate ^
         '.$path.'\image\ms_logo_displace_mask.png
          ');
    
     $process6->run();
     if (!$process6->isSuccessful()) {
         throw new ProcessFailedException($process6);
     }
         echo $process6->getOutput();
         echo '<img src="\image\ms_logo_displace_mask.png">';
         
         $process7 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         '.$path.'\image\ms_light_map_girl_black_polo.png ^
         -compose Multiply -composite ^
         '.$path.'\image\ms_logo_displace_mask.png ^
         -compose CopyOpacity -composite ^
         '.$path.'\image\ms_light_map_logo.png
         ');
   
    $process7->run();
    if (!$process7->isSuccessful()) {
        throw new ProcessFailedException($process7);
    }
        echo $process7->getOutput();
        echo '<img src="\image\ms_light_map_logo.png">';
        
  
        list($width, $height) = getimagesize($path.'\image\ms_light_map_logo.png');
        
    $X = 790 + (403-$width)/2;
    $Y = 930 +  (422-$height)/2;
        
  
         $process8 = new Process('magick convert ^
         '.$path.'\image\U-one-22.jpg ^
         '.$path.'\image\ms_light_map_logo.png ^
         -geometry +'.$X.'+'.$Y.' ^
         -compose over -composite ^
         -depth 16 ^
         '.$path.'\image\ms_product.png
         ');
   
    $process8->run();
    if (!$process8->isSuccessful()) {
        throw new ProcessFailedException($process8);
    }
        echo $process8->getOutput();
        echo '<img src="\image\ms_product.png">';
        
        
       /*  echo '<img src="data:image/jpg;base64,'.base64_encode($a1->getImageBlob()).'" alt="" />';   */
      }


      public function uploadMockup10(Request $request){
        $file = $request->file('file');
        $imageName =  $file->getClientOriginalName();
        $imageName = preg_replace('/\s+/', '', $imageName);
        $filename = pathinfo($imageName, PATHINFO_FILENAME);
        
        $extension =  $request->file('file')->getClientOriginalExtension();
        $image = $filename . "_" . time() . ".".$extension;
         $file->move('design/', $image); 
  
         $path = public_path();
  
         /* $process0 = new Process('magick convert  '.$path.'\image\flat_shirt.jpg 
         -resize 2000x3000
         '.$path.'\image\flat_shirt.jpg 
          ');
          $process0->run();
          if (!$process0->isSuccessful()) {
              throw new ProcessFailedException($process0);
          } */
  
    /*      $process = new Process('magick convert   '.$path.'\image\U-one-6.jpg[403x422+835+901] 
         -colorspace gray 
         -blur 10x250 
         -auto-level
         '.$path.'\image\displace_map_girl_red_regular.png
          ');
    
     $process->run();
     if (!$process->isSuccessful()) {
         throw new ProcessFailedException($process);
     }
         echo $process->getOutput();
         echo '<img src="\image\displace_map.png">'; */
  
         $imageName1 = "/" .  $image; 
  
         $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
         -resize 500x500
         '.$path.'\design' . $imageName1 . '
         '); 
         
      $process1->run();
       if (!$process1->isSuccessful()) {
           throw new ProcessFailedException($process1);    
     } 
  
  
         $process2 = new Process('magick convert 
         '.$path.'\design' . $imageName1 . '
         -bordercolor transparent -border 12x12 -thumbnail 403x422 
         '.$path.'\image\ms_temp.png
          ');
    
     $process2->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process2);
     }
         echo $process2->getOutput();
         echo '<img src="\image\ms_temp.png">';
  
        
  
            $process3 = new Process('magick convert 
         '.$path.'\image\flat_shirt.jpg[403x422+304+181] 
         -colorspace gray -blur 10x250 -auto-level 
         -depth 16 
         '.$path.'\image\ms_displace_map_flat_shirt.png
          ');
    
     $process3->run();
     if (!$process3->isSuccessful()) {
         throw new ProcessFailedException($process3);
     }
         echo $process3->getOutput();
         echo '<img src="\image\ms_displace_map_flat_shirt.png">';    
        
         $process4 = new Process('magick convert ^
         '.$path.'\image\ms_temp.png ^
         '.$path.'\image\ms_displace_map_flat_shirt.png ^
         -alpha set -virtual-pixel transparent ^
         -compose displace -set option:compose:args -5x-5 -composite ^
         -depth 16 ^
         '.$path.'\image\ms_displaced_logo.png
       
          ');
    
     $process4->run();
     if (!$process4->isSuccessful()) {
         throw new ProcessFailedException($process4);
     }
         echo $process4->getOutput();
         echo '<img src="\image\ms_displaced_logo.png">';
  
                
      $process5 = new Process('magick convert ^
         '.$path.'\image\flat_shirt.jpg[403x422+304+181] ^
         -colorspace gray -auto-level ^
         -blur 0x5 ^
         -contrast-stretch 0,50%% ^
         -depth 16 ^
         '.$path.'\image\ms_light_map_flat_shirt.png
          ');
  
      
    
     $process5->run();
     if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
     }
         echo $process5->getOutput();
         echo '<img src="\image\ms_light_map_flat_shirt.png">';  
          
         
         $process6 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         -channel matte -separate ^
         '.$path.'\image\ms_logo_displace_mask.png
          ');
    
     $process6->run();
     if (!$process6->isSuccessful()) {
         throw new ProcessFailedException($process6);
     }
         echo $process6->getOutput();
         echo '<img src="\image\ms_logo_displace_mask.png">';
         
         $process7 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         '.$path.'\image\ms_light_map_flat_shirt.png ^
         -compose Multiply -composite ^
         '.$path.'\image\ms_logo_displace_mask.png ^
         -compose CopyOpacity -composite ^
         '.$path.'\image\ms_light_map_logo.png
         ');
   
    $process7->run();
    if (!$process7->isSuccessful()) {
        throw new ProcessFailedException($process7);
    }
        echo $process7->getOutput();
        echo '<img src="\image\ms_light_map_logo.png">';
        
        
        list($width, $height) = getimagesize($path.'\image\ms_light_map_logo.png');
        
    $X = 304 + (403-$width)/2;
    $Y = 181 +  (422-$height)/2;
        
  
         $process8 = new Process('magick convert ^
         '.$path.'\image\flat_shirt.jpg ^
         '.$path.'\image\ms_light_map_logo.png ^
         -geometry +'.$X.'+'.$Y.' ^
         -compose over -composite ^
         -depth 16 ^
         '.$path.'\image\ms_product.png
         ');
   
    $process8->run();
    if (!$process8->isSuccessful()) {
        throw new ProcessFailedException($process8);
    }
        echo $process8->getOutput();
        echo '<img src="\image\ms_product.png">';
        
        
       /*  echo '<img src="data:image/jpg;base64,'.base64_encode($a1->getImageBlob()).'" alt="" />';   */
      }



      public function uploadMockup11(Request $request){
        $file = $request->file('file');
        $imageName =  $file->getClientOriginalName();
        $imageName = preg_replace('/\s+/', '', $imageName);
        $filename = pathinfo($imageName, PATHINFO_FILENAME);
        
        $extension =  $request->file('file')->getClientOriginalExtension();
        $image = $filename . "_" . time() . ".".$extension;
         $file->move('design/', $image); 
  
         $path = public_path();
  
         $process0 = new Process('magick convert  '.$path.'\image\U-one-3.jpg 
         -resize 1500x2500
         '.$path.'\image\U-one-3.jpg 
          ');
          $process0->run();
          if (!$process0->isSuccessful()) {
              throw new ProcessFailedException($process0);
          }

       /*    $process12 = new Process('magick convert  '.$path.'\image\U-one-23-edit.jpg 
          -resize 2000x3000
          '.$path.'\image\U-one-23-edit.jpg 
           ');
           $process12->run();
           if (!$process12->isSuccessful()) {
               throw new ProcessFailedException($process12);
           } */

          /*   */
  
         $process = new Process('magick convert   '.$path.'\image\U-one-3.jpg[303x322+595+551] 
         -colorspace gray 
         -blur 20x250 
         -auto-level
         '.$path.'\image\displace_map_girl_black_back_regular.png
          ');
    
     $process->run();
     if (!$process->isSuccessful()) {
         throw new ProcessFailedException($process);
     }
         echo $process->getOutput();
         echo '<img src="\image\displace_map_girl_black_back_regular.png">';
  
         $imageName1 = "/" .  $image; 
  
         $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
         -resize 300x300
         '.$path.'\design' . $imageName1 . '
         '); 
         
      $process1->run();
       if (!$process1->isSuccessful()) {
           throw new ProcessFailedException($process1);    
     } 
  
  
         $process2 = new Process('magick convert 
         '.$path.'\design' . $imageName1 . '
         -bordercolor transparent -border 12x12 -thumbnail 303x322 
         '.$path.'\image\ms_temp.png
          ');
    
     $process2->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process2);
     }
         echo $process2->getOutput();
         echo '<img src="\image\ms_temp.png">';
  
        
  
            $process3 = new Process('magick convert 
         '.$path.'\image\U-one-3.jpg[303x322+595+551] 
         -colorspace gray -blur 10x250 -auto-level 
         -depth 16 
         '.$path.'\image\ms_displace_map.png
          ');
    
     $process3->run();
     if (!$process3->isSuccessful()) {
         throw new ProcessFailedException($process3);
     }
         echo $process3->getOutput();
         echo '<img src="\image\ms_displace_map.png">';    
        
         $process4 = new Process('magick convert ^
         '.$path.'\image\ms_temp.png ^
         '.$path.'\image\ms_displace_map.png ^
         -alpha set -virtual-pixel transparent ^
         -compose displace -set option:compose:args -5x-5 -composite ^
         -depth 16 ^
         '.$path.'\image\ms_displaced_logo.png
       
          ');
    
     $process4->run();
     if (!$process4->isSuccessful()) {
         throw new ProcessFailedException($process4);
     }
         echo $process4->getOutput();
         echo '<img src="\image\ms_displaced_logo.png">';
  
                
      $process5 = new Process('magick convert ^
         '.$path.'\image\U-one-3.jpg[303x322+595+551] ^
         -colorspace gray -auto-level ^
         -blur 0x5 ^
         -contrast-stretch 0,80%% ^
         -depth 16 ^
         '.$path.'\image\ms_light_map.png
          ');
  
      
    
     $process5->run();
     if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
     }
         echo $process5->getOutput();
         echo '<img src="\image\ms_light_map.png">';  
          
         
         $process6 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         -channel matte -separate ^
         '.$path.'\image\ms_logo_displace_mask.png
          ');
    
     $process6->run();
     if (!$process6->isSuccessful()) {
         throw new ProcessFailedException($process6);
     }
         echo $process6->getOutput();
         echo '<img src="\image\ms_logo_displace_mask.png">';
         
         $process7 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         '.$path.'\image\ms_light_map.png ^
         -compose Multiply -composite ^
         '.$path.'\image\ms_logo_displace_mask.png ^
         -compose CopyOpacity -composite ^
         '.$path.'\image\ms_light_map_logo.png
         ');
   
    $process7->run();
    if (!$process7->isSuccessful()) {
        throw new ProcessFailedException($process7);
    }
        echo $process7->getOutput();
        echo '<img src="\image\ms_light_map_logo.png">';
        
        
        list($width, $height) = getimagesize($path.'\image\ms_light_map_logo.png');
       
    $X = 595 + (303-$width)/2;
    $Y = 551 +  (322-$height)/2;
        
  
         $process8 = new Process('magick convert ^
         '.$path.'\image\U-one-3.jpg ^
         '.$path.'\image\ms_light_map_logo.png ^
         -geometry +'.$X.'+'.$Y.' ^
         -compose over -composite ^
         -depth 16 ^
         '.$path.'\image\ms_product.png
         ');
   
    $process8->run();
    if (!$process8->isSuccessful()) {
        throw new ProcessFailedException($process8);
    }
        echo $process8->getOutput();
        echo '<img src="\image\ms_product.png">';
        
        
       /*  echo '<img src="data:image/jpg;base64,'.base64_encode($a1->getImageBlob()).'" alt="" />';   */
      }


      public function uploadMockup12(Request $request){
        $file = $request->file('file');
        $imageName =  $file->getClientOriginalName();
        $imageName = preg_replace('/\s+/', '', $imageName);
        $filename = pathinfo($imageName, PATHINFO_FILENAME);
        
        $extension =  $request->file('file')->getClientOriginalExtension();
        $image = $filename . "_" . time() . ".".$extension;
         $file->move('design/', $image); 
  
         $path = public_path();
  
         $process0 = new Process('magick convert  '.$path.'\image\U-one-15.jpg 
         -resize 1500x2500
         '.$path.'\image\U-one-15.jpg 
          ');
          $process0->run();
          if (!$process0->isSuccessful()) {
              throw new ProcessFailedException($process0);
          }

       /*    $process12 = new Process('magick convert  '.$path.'\image\U-one-23-edit.jpg 
          -resize 2000x3000
          '.$path.'\image\U-one-23-edit.jpg 
           ');
           $process12->run();
           if (!$process12->isSuccessful()) {
               throw new ProcessFailedException($process12);
           } */

          
  
         $process = new Process('magick convert   '.$path.'\image\U-one-15.jpg[303x322+625+601] 
         -colorspace gray 
         -blur 20x250 
         -auto-level
         '.$path.'\image\displace_map_girl_black_navy_regular.png
          ');
    
     $process->run();
     if (!$process->isSuccessful()) {
         throw new ProcessFailedException($process);
     }
         echo $process->getOutput();
         echo '<img src="\image\displace_map_girl_black_navy_regular.png">';
  
         $imageName1 = "/" .  $image; 
  
         $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
         -resize 300x300
         '.$path.'\design' . $imageName1 . '
         '); 
         
      $process1->run();
       if (!$process1->isSuccessful()) {
           throw new ProcessFailedException($process1);    
     } 
  
  
         $process2 = new Process('magick convert 
         '.$path.'\design' . $imageName1 . '
         -bordercolor transparent -border 12x12 -thumbnail 303x322 
         '.$path.'\image\ms_temp.png
          ');
    
     $process2->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process2);
     }
         echo $process2->getOutput();
         echo '<img src="\image\ms_temp.png">';
       
         list($width, $height) = getimagesize($path.'\image\ms_temp.png');
        
         $X = 625 + (303-$width)/2;
         $Y = 601 +  (322-$height)/2;
        
  
            $process3 = new Process('magick convert 
         '.$path.'\image\U-one-15.jpg[303x322+625+601] 
         -colorspace gray -blur 10x250 -auto-level 
         -depth 16 
         '.$path.'\image\ms_displace_map.png
          ');
    
     $process3->run();
     if (!$process3->isSuccessful()) {
         throw new ProcessFailedException($process3);
     }
         echo $process3->getOutput();
         echo '<img src="\image\ms_displace_map.png">';    
        
         $process4 = new Process('magick convert ^
         '.$path.'\image\ms_temp.png ^
         '.$path.'\image\ms_displace_map.png ^
         -alpha set -virtual-pixel transparent ^
         -compose displace -set option:compose:args -5x-5 -composite ^
         -depth 16 ^
         '.$path.'\image\ms_displaced_logo.png
       
          ');
    
     $process4->run();
     if (!$process4->isSuccessful()) {
         throw new ProcessFailedException($process4);
     }
         echo $process4->getOutput();
         echo '<img src="\image\ms_displaced_logo.png">';
  
                
      $process5 = new Process('magick convert ^
         '.$path.'\image\U-one-15.jpg[303x322+625+601] ^
         -colorspace gray -auto-level ^
         -blur 0x5 ^
         -contrast-stretch 0,80%% ^
         -depth 16 ^
         '.$path.'\image\ms_light_map.png
          ');
  
      
    
     $process5->run();
     if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
     }
         echo $process5->getOutput();
         echo '<img src="\image\ms_light_map.png">';  
          
         
         $process6 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         -channel matte -separate ^
         '.$path.'\image\ms_logo_displace_mask.png
          ');
    
     $process6->run();
     if (!$process6->isSuccessful()) {
         throw new ProcessFailedException($process6);
     }
         echo $process6->getOutput();
         echo '<img src="\image\ms_logo_displace_mask.png">';
         
         $process7 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         '.$path.'\image\ms_light_map.png ^
         -compose Multiply -composite ^
         '.$path.'\image\ms_logo_displace_mask.png ^
         -compose CopyOpacity -composite ^
         '.$path.'\image\ms_light_map_logo.png
         ');
   
    $process7->run();
    if (!$process7->isSuccessful()) {
        throw new ProcessFailedException($process7);
    }
        echo $process7->getOutput();
        echo '<img src="\image\ms_light_map_logo.png">';
        
   
        
  
         $process8 = new Process('magick convert ^
         '.$path.'\image\U-one-15.jpg ^
         '.$path.'\image\ms_light_map_logo.png ^
         -geometry +'.$X.'+'.$Y.' ^
         -compose over -composite ^
         -depth 16 ^
         '.$path.'\image\ms_product.png
         ');
   
    $process8->run();
    if (!$process8->isSuccessful()) {
        throw new ProcessFailedException($process8);
    }
        echo $process8->getOutput();
        echo '<img src="\image\ms_product.png">';
        
        
       /*  echo '<img src="data:image/jpg;base64,'.base64_encode($a1->getImageBlob()).'" alt="" />';   */
      }

      public function uploadMockup13(Request $request){
        $file = $request->file('file');
        $imageName =  $file->getClientOriginalName();
        $imageName = preg_replace('/\s+/', '', $imageName);
        $filename = pathinfo($imageName, PATHINFO_FILENAME);
        
        $extension =  $request->file('file')->getClientOriginalExtension();
        $image = $filename . "_" . time() . ".".$extension;
         $file->move('design/', $image); 
  
         $path = public_path();
  
         $process0 = new Process('magick convert  '.$path.'\image\U-one-8.jpg 
         -resize 1500x2500
         '.$path.'\image\U-one-8.jpg 
          ');
          $process0->run();
          if (!$process0->isSuccessful()) {
              throw new ProcessFailedException($process0);
          }

       /*    $process12 = new Process('magick convert  '.$path.'\image\U-one-23-edit.jpg 
          -resize 2000x3000
          '.$path.'\image\U-one-23-edit.jpg 
           ');
           $process12->run();
           if (!$process12->isSuccessful()) {
               throw new ProcessFailedException($process12);
           } */

          /*   */
  
         $process = new Process('magick convert   '.$path.'\image\U-one-8.jpg[303x322+625+601] 
         -colorspace gray 
         -blur 20x250 
         -auto-level
         '.$path.'\image\displace_map_girl_black_red_regular.png
          ');
    
     $process->run();
     if (!$process->isSuccessful()) {
         throw new ProcessFailedException($process);
     }
         echo $process->getOutput();
         echo '<img src="\image\displace_map_girl_black_red_regular.png">';
  
         $imageName1 = "/" .  $image; 
  
         $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
         -resize 300x300
         '.$path.'\design' . $imageName1 . '
         '); 
         
      $process1->run();
       if (!$process1->isSuccessful()) {
           throw new ProcessFailedException($process1);    
     } 
  
  
         $process2 = new Process('magick convert 
         '.$path.'\design' . $imageName1 . '
         -bordercolor transparent -border 12x12 -thumbnail 303x322 
         '.$path.'\image\ms_temp.png
          ');
    
     $process2->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process2);
     }
         echo $process2->getOutput();
         echo '<img src="\image\ms_temp.png">';

         list($width, $height) = getimagesize($path.'\image\ms_temp.png');
        
         $X = 625 + (303-$width)/2;
         $Y = 601 +  (322-$height)/2;
  
        
  
            $process3 = new Process('magick convert 
         '.$path.'\image\U-one-8.jpg[303x322+'.$X.'+'.$Y.'] 
         -colorspace gray -blur 10x250 -auto-level 
         -depth 16 
         '.$path.'\image\ms_displace_map.png
          ');
    
     $process3->run();
     if (!$process3->isSuccessful()) {
         throw new ProcessFailedException($process3);
     }
         echo $process3->getOutput();
         echo '<img src="\image\ms_displace_map.png">';    
        
         $process4 = new Process('magick convert ^
         '.$path.'\image\ms_temp.png ^
         '.$path.'\image\ms_displace_map.png ^
         -alpha set -virtual-pixel transparent ^
         -compose displace -set option:compose:args -5x-5 -composite ^
         -depth 16 ^
         '.$path.'\image\ms_displaced_logo.png
       
          ');
    
     $process4->run();
     if (!$process4->isSuccessful()) {
         throw new ProcessFailedException($process4);
     }
         echo $process4->getOutput();
         echo '<img src="\image\ms_displaced_logo.png">';
  
                
      $process5 = new Process('magick convert ^
         '.$path.'\image\U-one-8.jpg[303x322+'.$X.'+'.$Y.'] ^
         -colorspace gray -auto-level ^
         -blur 0x5 ^
         -contrast-stretch 0,50%% ^
         -depth 16 ^
         '.$path.'\image\ms_light_map.png
          ');
  
      
    
     $process5->run();
     if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
     }
         echo $process5->getOutput();
         echo '<img src="\image\ms_light_map.png">';  
          
         
         $process6 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         -channel matte -separate ^
         '.$path.'\image\ms_logo_displace_mask.png
          ');
    
     $process6->run();
     if (!$process6->isSuccessful()) {
         throw new ProcessFailedException($process6);
     }
         echo $process6->getOutput();
         echo '<img src="\image\ms_logo_displace_mask.png">';

        
         
         $process7 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         '.$path.'\image\ms_light_map.png ^
         -compose Multiply  -composite ^ -geometry +'.$X.'+'.$Y.' ^
         '.$path.'\image\ms_logo_displace_mask.png ^
         -compose CopyOpacity  -composite ^ -geometry +'.$X.'+'.$Y.' ^
         '.$path.'\image\ms_light_map_logo.png
         ');
   
    $process7->run();
    if (!$process7->isSuccessful()) {
        throw new ProcessFailedException($process7);
    }
        echo $process7->getOutput();
        echo '<img src="\image\ms_light_map_logo.png">';
        
    /*     
        list($width, $height) = getimagesize($path.'\image\ms_light_map_logo.png');
        
    $X = 625 + (303-$width)/2;
    $Y = 731 +  (322-$height)/2; */
        
  
         $process8 = new Process('magick convert ^
         '.$path.'\image\U-one-8.jpg ^
         '.$path.'\image\ms_light_map_logo.png ^
         -geometry +'.$X.'+'.$Y.' ^
         -compose over -composite ^
         -depth 16 ^
         '.$path.'\image\ms_product.png
         ');
   
    $process8->run();
    if (!$process8->isSuccessful()) {
        throw new ProcessFailedException($process8);
    }
        echo $process8->getOutput();
        echo '<img src="\image\ms_product.png">';
        
        
       /*  echo '<img src="data:image/jpg;base64,'.base64_encode($a1->getImageBlob()).'" alt="" />';   */
      }

      public function uploadMockup14(Request $request){
        $file = $request->file('file');
        $imageName =  $file->getClientOriginalName();
        $imageName = preg_replace('/\s+/', '', $imageName);
        $filename = pathinfo($imageName, PATHINFO_FILENAME);
        
        $extension =  $request->file('file')->getClientOriginalExtension();
        $image = $filename . "_" . time() . ".".$extension;
         $file->move('design/', $image); 
  
         $path = public_path();
  
         $process0 = new Process('magick convert  '.$path.'\image\U-one-18.jpg 
         -resize 1500x2500
         '.$path.'\image\U-one-18.jpg 
          ');
          $process0->run();
          if (!$process0->isSuccessful()) {
              throw new ProcessFailedException($process0);
          }

       /*    $process12 = new Process('magick convert  '.$path.'\image\U-one-23-edit.jpg 
          -resize 2000x3000
          '.$path.'\image\U-one-23-edit.jpg 
           ');
           $process12->run();
           if (!$process12->isSuccessful()) {
               throw new ProcessFailedException($process12);
           } */

          /*   */
  
         $process = new Process('magick convert   '.$path.'\image\U-one-18.jpg[303x322+620+590] 
         -colorspace gray 
         -blur 20x250 
         -auto-level
         '.$path.'\image\displace_map_girl_back_white_regular.png
          ');
    
     $process->run();
     if (!$process->isSuccessful()) {
         throw new ProcessFailedException($process);
     }
         echo $process->getOutput();
         echo '<img src="\image\displace_map_girl_back_white_regular.png">';
  
         $imageName1 = "/" .  $image; 
  
         $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
         -resize 300x300
         '.$path.'\design' . $imageName1 . '
         '); 
         
      $process1->run();
       if (!$process1->isSuccessful()) {
           throw new ProcessFailedException($process1);    
     } 
  
  
         $process2 = new Process('magick convert 
         '.$path.'\design' . $imageName1 . '
         -bordercolor transparent -border 12x12 -thumbnail 303x322 
         '.$path.'\image\ms_temp.png
          ');
    
     $process2->run();
     if (!$process1->isSuccessful()) {
         throw new ProcessFailedException($process2);
     }
         echo $process2->getOutput();
         echo '<img src="\image\ms_temp.png">';
  
        
  
            $process3 = new Process('magick convert 
         '.$path.'\image\U-one-18.jpg[303x322+620+590] 
         -colorspace gray -blur 10x250 -auto-level 
         -depth 16 
         '.$path.'\image\ms_displace_map.png
          ');
    
     $process3->run();
     if (!$process3->isSuccessful()) {
         throw new ProcessFailedException($process3);
     }
         echo $process3->getOutput();
         echo '<img src="\image\ms_displace_map.png">';    
        
         $process4 = new Process('magick convert ^
         '.$path.'\image\ms_temp.png ^
         '.$path.'\image\ms_displace_map.png ^
         -alpha set -virtual-pixel transparent ^
         -compose displace -set option:compose:args -5x-5 -composite ^
         -depth 16 ^
         '.$path.'\image\ms_displaced_logo.png
       
          ');
    
     $process4->run();
     if (!$process4->isSuccessful()) {
         throw new ProcessFailedException($process4);
     }
         echo $process4->getOutput();
         echo '<img src="\image\ms_displaced_logo.png">';
  
                
      $process5 = new Process('magick convert ^
         '.$path.'\image\U-one-18.jpg[303x322+620+590] ^
         -colorspace gray -auto-level ^
         -blur 0x5 ^
         -contrast-stretch 0,50%% ^
         -depth 16 ^
         '.$path.'\image\ms_light_map.png
          ');
  
      
    
     $process5->run();
     if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
     }
         echo $process5->getOutput();
         echo '<img src="\image\ms_light_map.png">';  
          
         
         $process6 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         -channel matte -separate ^
         '.$path.'\image\ms_logo_displace_mask.png
          ');
    
     $process6->run();
     if (!$process6->isSuccessful()) {
         throw new ProcessFailedException($process6);
     }
         echo $process6->getOutput();
         echo '<img src="\image\ms_logo_displace_mask.png">';

         list($width, $height) = getimagesize($path.'\image\ms_displaced_logo.png');
        
         $X = 620 + (303-$width)/2;
         $Y = 590 +  (322-$height)/2;
         
         $process7 = new Process('magick convert ^
         '.$path.'\image\ms_displaced_logo.png ^
         '.$path.'\image\ms_light_map.png ^
         -compose Multiply  -composite ^ -geometry +'.$X.'+'.$Y.' ^
         '.$path.'\image\ms_logo_displace_mask.png ^
         -compose CopyOpacity  -composite ^ -geometry +'.$X.'+'.$Y.' ^
         '.$path.'\image\ms_light_map_logo.png
         ');
   
    $process7->run();
    if (!$process7->isSuccessful()) {
        throw new ProcessFailedException($process7);
    }
        echo $process7->getOutput();
        echo '<img src="\image\ms_light_map_logo.png">';
        
    /*     
        list($width, $height) = getimagesize($path.'\image\ms_light_map_logo.png');
        
    $X = 625 + (303-$width)/2;
    $Y = 731 +  (322-$height)/2; */
        
  
         $process8 = new Process('magick convert ^
         '.$path.'\image\U-one-18.jpg ^
         '.$path.'\image\ms_light_map_logo.png ^
         -geometry +'.$X.'+'.$Y.' ^
         -compose over -composite ^
         -depth 16 ^
         '.$path.'\image\ms_product.png
         ');
   
    $process8->run();
    if (!$process8->isSuccessful()) {
        throw new ProcessFailedException($process8);
    }
        echo $process8->getOutput();
        echo '<img src="\image\ms_product.png">';
        
        
       /*  echo '<img src="data:image/jpg;base64,'.base64_encode($a1->getImageBlob()).'" alt="" />';   */
      }


      public function uploadMockup15(Request $request){
      
      
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
       $src1->resizeImage(400, null,\Imagick::FILTER_LANCZOS,1); 
       $src1->writeImage(public_path("design". $imageName1));
      $src2 = new \Imagick(public_path("\image\Samsung-P20-Bezpozadine.png"));
      
      
      
       $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
      
       $process5 = new Process('magick convert ^
      '.$path.'\image\Samsung-P20-Bezpozadine.png ^
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
       -geometry -340-320 ^
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
      $src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 340, 320);
      $src2->writeImage(public_path("image/output1.png"));
       $process5 = new Process('magick  convert '.$path.'\image\output1.png 
       -flatten  '.$path.'\image\out.png 
      ');
         $process5->run();
            if (!$process5->isSuccessful()) {
             throw new ProcessFailedException($process5);
            }
             echo $process5->getOutput();
             echo '<img src="\image\out.png">'; 
  
             $process10 = new Process('magick  convert '.$path.'\image\Samsung-P20-Bezpozadine.png -background "rgb(0,0,0)" 
             -flatten  '.$path.'\image\SamsungGalaxy-P20-Crna.png
            ');
               $process10->run();
                  if (!$process10->isSuccessful()) {
                   throw new ProcessFailedException($process10);
                  }
                   echo $process10->getOutput();
                   echo '<img src="\image\SamsungGalaxy-P20-Crna.png">'; 
    
     
      /* $src1 = new \Imagick(public_path("\image\ms_light_map_logo_phone.png")); */
     
      
      $process8 = new Process('magick convert ^
      '.$path.'\image\SamsungGalaxy-P20-Crna.png ^
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
      }

      public function uploadMockup16(Request $request){
      
      
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

        
     
        
  
   $src1 = new \Imagick(public_path("design". $imageName1));
   $src1->resizeImage(400, null,\Imagick::FILTER_LANCZOS,1); 
   $src1->writeImage(public_path("design". $imageName1));
  $src2 = new \Imagick(public_path("\site-images\Samsung-S20Plus-Bezpozadine.png"));
  
  
  
   $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
  
   $process5 = new Process('magick convert ^
  '.$path.'\site-images\Samsung-S20Plus-Bezpozadine.png ^
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
   -geometry -340-320 ^
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
  $src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 340, 320);
  $src2->writeImage(public_path("image/output1.png"));
   $process5 = new Process('magick  convert '.$path.'\image\output1.png 
   -flatten  '.$path.'\image\out.png 
  ');
     $process5->run();
        if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
        }
         echo $process5->getOutput();
         echo '<img src="\image\out.png">'; 

         $process10 = new Process('magick  convert '.$path.'\site-images\Samsung-S20Plus-Bezpozadine.png -background "rgb(0,0,0)" 
         -flatten  '.$path.'\image\Samsung-S20Plus-Crna.png
        ');
           $process10->run();
              if (!$process10->isSuccessful()) {
               throw new ProcessFailedException($process10);
              }
               echo $process10->getOutput();
               echo '<img src="\image\Samsung-S20Plus-Crna.png">'; 

 
  /* $src1 = new \Imagick(public_path("\image\ms_light_map_logo_phone.png")); */
 
  
  $process8 = new Process('magick convert ^
  '.$path.'\image\Samsung-S20Plus-Crna.png ^
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
  }

  public function uploadMockup17(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
   // dd($extension);
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

    /*  $process0 = new Process(' convert -version
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 
      echo $process0->getOutput();

      dd(); */
     $process0 = new Process('magick convert '.$path.'\site-images\U1-Obicnamajica-Bijela-Frontalno1.jpg 
     -resize 1500x2500
     '.$path.'\site-images\U1-Obicnamajica-Bijela-Frontalno1.jpg 
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 

      $process = new Process('magick convert   '.$path.'\site-images\U1-Obicnamajica-Bijela-Frontalno1.jpg[303x322+570+541] 
     -colorspace gray 
     -blur 10x250 
     -auto-level
     '.$path.'\image\displace_map.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map.png">'; 

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 300x300
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 303x322 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';


     list($width, $height) = getimagesize($path.'\image\ms_temp.png');

   
     $X = 570 + (303-$width)/2;
     $Y = 541 +  (322-$height)/2;
    

      $process3 = new Process('magick convert 
     '.$path.'\site-images\U1-Obicnamajica-Bijela-Frontalno1.jpg[303x322+570+541] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map_man_white_regular.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map_man_white_regular.png">'; 
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map_man_white_regular.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

     
      $process5 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Bijela-Frontalno1.jpg[303x322+570+541] ^
     -colorspace gray -auto-level ^
     -blur 0x4 ^
     -contrast-stretch 0,30%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map_man_white_regular.png
      ');

/*         Makao sam komandu -separate proces 5 */

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map_man_white_regular.png">'; 
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map_man_white_regular.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    
   
   /*  -geometry +984+1101 */

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Bijela-Frontalno1.jpg ^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.'
     -compose over -composite ^
     -depth 16 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';

  }


  public function uploadMockup18(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

     $process0 = new Process('magick convert  '.$path.'\site-images\U1-Obicnamajica-Bijela-Pozadi.jpg 
     -resize 1500x2500
     '.$path.'\site-images\U1-Obicnamajica-Bijela-Pozadi.jpg 
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      }

   /*    $process12 = new Process('magick convert  '.$path.'\image\U-one-23-edit.jpg 
      -resize 2000x3000
      '.$path.'\image\U-one-23-edit.jpg 
       ');
       $process12->run();
       if (!$process12->isSuccessful()) {
           throw new ProcessFailedException($process12);
       } */

      

     $process = new Process('magick convert   '.$path.'\site-images\U1-Obicnamajica-Bijela-Pozadi.jpg[303x322+600+601] 
     -colorspace gray 
     -blur 20x250 
     -auto-level
     '.$path.'\image\displace_map_man_back_white_regular.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map_man_back_white_regular.png">';

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 300x300
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 303x322 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';
   
     list($width, $height) = getimagesize($path.'\image\ms_temp.png');
    
     $X = 600 + (303-$width)/2;
     $Y = 601 +  (322-$height)/2;
    

        $process3 = new Process('magick convert 
     '.$path.'\site-images\U1-Obicnamajica-Bijela-Pozadi.jpg[303x322+600+601] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map.png">';    
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

            
  $process5 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Bijela-Pozadi.jpg[303x322+600+601] ^
     -colorspace gray -auto-level ^
     -blur 0x5 ^
     -contrast-stretch 0,80%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map.png
      ');

  

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map.png">';  
      
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    

    

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Bijela-Pozadi.jpg ^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.' ^
     -compose over -composite ^
     -depth 16 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';
    
  }


  public function uploadMockup19(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
   // dd($extension);
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

    /*  $process0 = new Process(' convert -version
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 
      echo $process0->getOutput();

      dd(); */
     $process0 = new Process('magick convert '.$path.'\site-images\U1-Obicnamajica-Crna-Frontalno.jpg 
     -resize 1500x2500
     '.$path.'\site-images\U1-Obicnamajica-Crna-Frontalno.jpg 
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 

      $process = new Process('magick convert   '.$path.'\site-images\U1-Obicnamajica-Crna-Frontalno.jpg[303x322+590+541] 
     -colorspace gray 
     -blur 10x250 
     -auto-level
     '.$path.'\image\displace_map.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map.png">'; 

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 300x300
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 303x322 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';


     list($width, $height) = getimagesize($path.'\image\ms_temp.png');

   
     $X = 590 + (303-$width)/2;
     $Y = 541 +  (322-$height)/2;
    

      $process3 = new Process('magick convert 
     '.$path.'\site-images\U1-Obicnamajica-Crna-Frontalno.jpg[303x322+590+541] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map_man_white_regular.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map_man_white_regular.png">'; 
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map_man_white_regular.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

     
      $process5 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Crna-Frontalno.jpg[303x322+590+541] ^
     -colorspace gray -auto-level ^
     -blur 0x4 ^
     -contrast-stretch 0,30%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map_man_white_regular.png
      ');

/*         Makao sam komandu -separate proces 5 */

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map_man_white_regular.png">'; 
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map_man_white_regular.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    
   
   /*  -geometry +984+1101 */

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Crna-Frontalno.jpg ^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.'
     -compose over -composite ^
     -depth 16 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';
  }

  public function uploadMockup20(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

     $process0 = new Process('magick convert  '.$path.'\site-images\U1-Obicnamajica-Crna-Pozadi.jpg 
     -resize 1500x2500
     '.$path.'\site-images\U1-Obicnamajica-Crna-Pozadi.jpg 
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      }

   /*    $process12 = new Process('magick convert  '.$path.'\image\U-one-23-edit.jpg 
      -resize 2000x3000
      '.$path.'\image\U-one-23-edit.jpg 
       ');
       $process12->run();
       if (!$process12->isSuccessful()) {
           throw new ProcessFailedException($process12);
       } */

      

     $process = new Process('magick convert   '.$path.'\site-images\U1-Obicnamajica-Crna-Pozadi.jpg[303x322+600+601] 
     -colorspace gray 
     -blur 20x250 
     -auto-level
     '.$path.'\image\displace_map_man_back_white_regular.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map_man_back_white_regular.png">';

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 300x300
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 303x322 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';
   
     list($width, $height) = getimagesize($path.'\image\ms_temp.png');
    
     $X = 600 + (303-$width)/2;
     $Y = 601 +  (322-$height)/2;
    

        $process3 = new Process('magick convert 
     '.$path.'\site-images\U1-Obicnamajica-Crna-Pozadi.jpg[303x322+600+601] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map.png">';    
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

            
  $process5 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Crna-Pozadi.jpg[303x322+600+601] ^
     -colorspace gray -auto-level ^
     -blur 0x5 ^
     -contrast-stretch 0,80%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map.png
      ');

  

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map.png">';  
      
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    

    

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Crna-Pozadi.jpg ^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.' ^
     -compose over -composite ^
     -depth 16 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';
  }


  public function uploadMockup21(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
   // dd($extension);
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

    /*  $process0 = new Process(' convert -version
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 
      echo $process0->getOutput();

      dd(); */
     $process0 = new Process('magick convert '.$path.'\site-images\U1-Obicnamajica-Crvena-Frontalno.jpg 
     -resize 1500x2500
     '.$path.'\site-images\U1-Obicnamajica-Crvena-Frontalno.jpg 
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 

      $process = new Process('magick convert   '.$path.'\site-images\U1-Obicnamajica-Crvena-Frontalno.jpg[303x322+590+541] 
     -colorspace gray 
     -blur 10x250 
     -auto-level
     '.$path.'\image\displace_map.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map.png">'; 

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 300x300
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 303x322 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';


     list($width, $height) = getimagesize($path.'\image\ms_temp.png');

   
     $X = 590 + (303-$width)/2;
     $Y = 541 +  (322-$height)/2;
    

      $process3 = new Process('magick convert 
     '.$path.'\site-images\U1-Obicnamajica-Crvena-Frontalno.jpg[303x322+590+541] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map_man_white_regular.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map_man_white_regular.png">'; 
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map_man_white_regular.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

     
      $process5 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Crvena-Frontalno.jpg[303x322+590+541] ^
     -colorspace gray -auto-level ^
     -blur 0x4 ^
     -contrast-stretch 0,30%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map_man_white_regular.png
      ');

/*         Makao sam komandu -separate proces 5 */

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map_man_white_regular.png">'; 
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map_man_white_regular.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    
   
   /*  -geometry +984+1101 */

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Crvena-Frontalno.jpg ^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.'
     -compose over -composite ^
     -depth 16 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';
  }


  public function uploadMockup22(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

     $process0 = new Process('magick convert  '.$path.'\site-images\U1-Obicnamajica-Crvena-Pozadi.jpg 
     -resize 1500x2500
     '.$path.'\site-images\U1-Obicnamajica-Crvena-Pozadi.jpg 
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      }

   /*    $process12 = new Process('magick convert  '.$path.'\image\U-one-23-edit.jpg 
      -resize 2000x3000
      '.$path.'\image\U-one-23-edit.jpg 
       ');
       $process12->run();
       if (!$process12->isSuccessful()) {
           throw new ProcessFailedException($process12);
       } */

      

     $process = new Process('magick convert   '.$path.'\site-images\U1-Obicnamajica-Crvena-Pozadi.jpg[303x322+610+601] 
     -colorspace gray 
     -blur 20x250 
     -auto-level
     '.$path.'\image\displace_map_man_back_white_regular.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map_man_back_white_regular.png">';

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 300x300
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 303x322 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';
   
     list($width, $height) = getimagesize($path.'\image\ms_temp.png');
    
     $X = 610 + (303-$width)/2;
     $Y = 601 +  (322-$height)/2;
    

        $process3 = new Process('magick convert 
     '.$path.'\site-images\U1-Obicnamajica-Crvena-Pozadi.jpg[303x322+610+601] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map.png">';    
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

            
  $process5 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Crvena-Pozadi.jpg[303x322+610+601] ^
     -colorspace gray -auto-level ^
     -blur 0x5 ^
     -contrast-stretch 0,80%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map.png
      ');

  

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map.png">';  
      
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    

    

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Crvena-Pozadi.jpg ^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.' ^
     -compose over -composite ^
     -depth 16 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';
  }


  public function uploadMockup23(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
   // dd($extension);
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

    /*  $process0 = new Process(' convert -version
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 
      echo $process0->getOutput();

      dd(); */
     $process0 = new Process('magick convert '.$path.'\site-images\U1-Obicnamajica-Teget-Frontalno.jpg 
     -resize 1500x2500
     '.$path.'\site-images\U1-Obicnamajica-Teget-Frontalno.jpg 
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 

      $process = new Process('magick convert   '.$path.'\site-images\U1-Obicnamajica-Teget-Frontalno.jpg[303x322+610+541] 
     -colorspace gray 
     -blur 10x250 
     -auto-level
     '.$path.'\image\displace_map.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map.png">'; 

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 300x300
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 303x322 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';


     list($width, $height) = getimagesize($path.'\image\ms_temp.png');

   
     $X = 610 + (303-$width)/2;
     $Y = 541 +  (322-$height)/2;
    

      $process3 = new Process('magick convert 
     '.$path.'\site-images\U1-Obicnamajica-Teget-Frontalno.jpg[303x322+610+541] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map_man_white_regular.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map_man_white_regular.png">'; 
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map_man_white_regular.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

     
      $process5 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Teget-Frontalno.jpg[303x322+610+541] ^
     -colorspace gray -auto-level ^
     -blur 0x4 ^
     -contrast-stretch 0,30%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map_man_white_regular.png
      ');

/*         Makao sam komandu -separate proces 5 */

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map_man_white_regular.png">'; 
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map_man_white_regular.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    
   
   /*  -geometry +984+1101 */

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Teget-Frontalno.jpg ^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.'
     -compose over -composite ^
     -depth 16 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';
  }

  public function uploadMockup24(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

     $process0 = new Process('magick convert  '.$path.'\site-images\U1-Obicnamajica-Teget-Pozadi.jpg 
     -resize 1500x2500
     '.$path.'\site-images\U1-Obicnamajica-Teget-Pozadi.jpg 
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      }

   /*    $process12 = new Process('magick convert  '.$path.'\image\U-one-23-edit.jpg 
      -resize 2000x3000
      '.$path.'\image\U-one-23-edit.jpg 
       ');
       $process12->run();
       if (!$process12->isSuccessful()) {
           throw new ProcessFailedException($process12);
       } */

      

     $process = new Process('magick convert   '.$path.'\site-images\U1-Obicnamajica-Teget-Pozadi.jpg[303x322+610+601] 
     -colorspace gray 
     -blur 20x250 
     -auto-level
     '.$path.'\image\displace_map_man_back_white_regular.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map_man_back_white_regular.png">';

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 300x300
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 303x322 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';
   
     list($width, $height) = getimagesize($path.'\image\ms_temp.png');
    
     $X = 610 + (303-$width)/2;
     $Y = 601 +  (322-$height)/2;
    

        $process3 = new Process('magick convert 
     '.$path.'\site-images\U1-Obicnamajica-Teget-Pozadi.jpg[303x322+610+601] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map.png">';    
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

            
  $process5 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Teget-Pozadi.jpg[303x322+610+601] ^
     -colorspace gray -auto-level ^
     -blur 0x5 ^
     -contrast-stretch 0,80%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map.png
      ');

  

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map.png">';  
      
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    

    

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\U1-Obicnamajica-Teget-Pozadi.jpg ^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.' ^
     -compose over -composite ^
     -depth 16 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';
  }


  public function uploadMockup25(Request $request){
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

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(450, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
    $src2 = new \Imagick(public_path("\site-images\Poster-CrniRam-A3.jpg"));
  
    
    
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
    
     $process5 = new Process('magick convert ^
    '.$path.'\site-images\Poster-CrniRam-A3.jpg ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-crni-ram.png
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
     echo '<img src="\image\ms_light_map-crni-ram.png">';
    
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask-crni-ram.png
      ');
  
     
    
    $process6->run();
    if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
    }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask-crni-ram.png">';
    
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-crni-ram.png ^
     -geometry -1063-720 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask-crni-ram.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo-crni-ram.png
     ');
    
    $process7->run();
    if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
    }
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo-crni-ram.png">';
    
    $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
    $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
    $src = new \Imagick(public_path("\image\ms_light_map_logo-crni-ram.png"));
    $src2->compositeImage($src, \Imagick::COMPOSITE_ATOP , 1063, 720);
    $src2->writeImage(public_path("image/output1-ram.png"));

    echo '<img src="/image/output1-ram.png" alt="" />'; 
  }


  public function uploadMockup26(Request $request){
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

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(700, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
    $src2 = new \Imagick(public_path("\site-images\Poster---Bijeli-Ram---A3.jpg"));
  
    
    
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
    
     $process5 = new Process('magick convert ^
    '.$path.'\site-images\Poster---Bijeli-Ram---A3.jpg ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-bijeli-ram.png
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
     echo '<img src="\image\ms_light_map-bijeli-ram.png">';
    
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask-bijeli-ram.png
      ');
  
     
    
    $process6->run();
    if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
    }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask-bijeli-ram.png">';
    
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-bijeli-ram.png ^
     -geometry -700-550 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask-bijeli-ram.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo-bijeli-ram.png
     ');
    
    $process7->run();
    if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
    }
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo-bijeli-ram.png">';
    
    $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
    $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
    $src = new \Imagick(public_path("\image\ms_light_map_logo-bijeli-ram.png"));
    $src2->compositeImage($src, \Imagick::COMPOSITE_ATOP , 700, 550);
    $src2->writeImage(public_path("image/output1-bijeli-ram.png"));

    echo '<img src="/image/output1-bijeli-ram.png" alt="" />'; 
  }

  public function uploadMockup27(Request $request){
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

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(800, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
    $src2 = new \Imagick(public_path("\site-images\Poster---Bijeli-Ram---A3---B2---B1---THUMBNAIL.jpg"));
  
    
    
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
    
     $process5 = new Process('magick convert ^
    '.$path.'\site-images\Poster---Bijeli-Ram---A3---B2---B1---THUMBNAIL.jpg ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-bijeli-ram-thumb.png
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
     echo '<img src="\image\ms_light_map-bijeli-ram-thumb.png">';
    
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask-bijeli-ram-thumb.png
      ');
  
     
    
    $process6->run();
    if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
    }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask-bijeli-ram-thumb.png">';
    
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-bijeli-ram-thumb.png ^
     -geometry -630-550 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask-bijeli-ram-thumb.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo-bijeli-ram-thumb.png
     ');
    
    $process7->run();
    if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
    }
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo-bijeli-ram-thumb.png">';
    
    $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
    $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
    $src = new \Imagick(public_path("\image\ms_light_map_logo-bijeli-ram-thumb.png"));
    $src2->compositeImage($src, \Imagick::COMPOSITE_ATOP , 630, 550);
    $src2->writeImage(public_path("image/output1-bijeli-ram-thumb.png"));

    echo '<img src="/image/output1-bijeli-ram-thumb.png" alt="" />'; 
  }

  public function uploadMockup28(Request $request){
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

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(800, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
    $src2 = new \Imagick(public_path("\site-images\Poster---Bijeli-Ram---B1.jpg"));
  
    
    
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
    
     $process5 = new Process('magick convert ^
    '.$path.'\site-images\Poster---Bijeli-Ram---B1.jpg ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-bijeli-ram-b1.png
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
     echo '<img src="\image\ms_light_map-bijeli-ram-b1.png">';
    
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask-bijeli-ram-b1.png
      ');
  
     
    
    $process6->run();
    if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
    }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask-bijeli-ram-b1.png">';
    
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-bijeli-ram-b1.png ^
     -geometry -900-550 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask-bijeli-ram-b1.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo-bijeli-ram-b1.png
     ');
    
    $process7->run();
    if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
    }
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo-bijeli-ram-b1.png">';
    
    $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
    $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
    $src = new \Imagick(public_path("\image\ms_light_map_logo-bijeli-ram-b1.png"));
    $src2->compositeImage($src, \Imagick::COMPOSITE_ATOP , 900, 550);
    $src2->writeImage(public_path("image/output1-bijeli-ram-b1.png"));

    echo '<img src="/image/output1-bijeli-ram-b1.png" alt="" />'; 
  }


  public function uploadMockup29(Request $request){
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

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(700, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
    $src2 = new \Imagick(public_path("\site-images\Poster---Bijeli-Ram---B2.jpg"));
  
    
    
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
    
     $process5 = new Process('magick convert ^
    '.$path.'\site-images\Poster---Bijeli-Ram---B2.jpg ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-bijeli-ram-b2.png
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
     echo '<img src="\image\ms_light_map-bijeli-ram-b2.png">';
    
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask-bijeli-ram-b2.png
      ');
  
     
    
    $process6->run();
    if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
    }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask-bijeli-ram-b2.png">';
    
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-bijeli-ram-b2.png ^
     -geometry -670-820 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask-bijeli-ram-b2.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo-bijeli-ram-b2.png
     ');
    
    $process7->run();
    if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
    }
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo-bijeli-ram-b2.png">';
    
    $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
    $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
    $src = new \Imagick(public_path("\image\ms_light_map_logo-bijeli-ram-b2.png"));
    $src2->compositeImage($src, \Imagick::COMPOSITE_ATOP , 670, 820);
    $src2->writeImage(public_path("image/output1-bijeli-ram-b2.png"));

    echo '<img src="/image/output1-bijeli-ram-b2.png" alt="" />'; 
  }

  public function uploadMockup30(Request $request){
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

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(500, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
    $src2 = new \Imagick(public_path("\site-images\Poster---Crni-Ram---A3.jpg"));
  
    
    
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
    
     $process5 = new Process('magick convert ^
    '.$path.'\site-images\Poster---Crni-Ram---A3.jpg ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-crni-ram-a3.png
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
     echo '<img src="\image\ms_light_map-crni-ram-a3.png">';
    
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask-crni-ram-a3.png
      ');
  
     
    
    $process6->run();
    if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
    }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask-crni-ram-a3.png">';
    
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-crni-ram-a3.png ^
     -geometry -1035-680 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask-crni-ram-a3.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo-crni-ram-a3.png
     ');
    
    $process7->run();
    if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
    }
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo-crni-ram-a3.png">';
    
    $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
    $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
    $src = new \Imagick(public_path("\image\ms_light_map_logo-crni-ram-a3.png"));
    $src2->compositeImage($src, \Imagick::COMPOSITE_ATOP , 1035, 680);
    $src2->writeImage(public_path("image/output1-crni-ram-a3.png"));

    echo '<img src="/image/output1-crni-ram-a3.png" alt="" />'; 
  }


  public function uploadMockup31(Request $request){
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

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(700, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
    $src2 = new \Imagick(public_path("\site-images\Poster---Crni-Ram---A3---B2---B1---THUMBNAIL.jpg"));
  
    
    
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
    
     $process5 = new Process('magick convert ^
    '.$path.'\site-images\Poster---Crni-Ram---A3---B2---B1---THUMBNAIL.jpg ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-crni-ram-thumb.png
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
     echo '<img src="\image\ms_light_map-crni-ram-thumb.png">';
    
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask-crni-ram-thumb.png
      ');
  
     
    
    $process6->run();
    if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
    }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask-crni-ram-thumb.png">';
    
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-crni-ram-thumb.png ^
     -geometry -670-650 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask-crni-ram-thumb.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo-crni-ram-thumb.png
     ');
    
    $process7->run();
    if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
    }
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo-crni-ram-thumb.png">';
    
    $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
    $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
    $src = new \Imagick(public_path("\image\ms_light_map_logo-crni-ram-thumb.png"));
    $src2->compositeImage($src, \Imagick::COMPOSITE_ATOP , 670, 650);
    $src2->writeImage(public_path("image/output1-crni-ram-thumb.png"));

    echo '<img src="/image/output1-crni-ram-thumb.png" alt="" />'; 
  }


  public function uploadMockup32(Request $request){
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

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(700, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
    $src2 = new \Imagick(public_path("\site-images\Poster---Crni-Ram---B1.jpg"));
  
    
    
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
    
     $process5 = new Process('magick convert ^
    '.$path.'\site-images\Poster---Crni-Ram---B1.jpg ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-crni-ram-b1.png
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
     echo '<img src="\image\ms_light_map-crni-ram-b1.png">';
    
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask-crni-ram-b1.png
      ');
  
     
    
    $process6->run();
    if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
    }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask-crni-ram-b1.png">';
    
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-crni-ram-b1.png ^
     -geometry -270-250 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask-crni-ram-b1.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo-crni-ram-b1.png
     ');
    
    $process7->run();
    if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
    }
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo-crni-ram-b1.png">';
    
    $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
    $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
    $src = new \Imagick(public_path("\image\ms_light_map_logo-crni-ram-b1.png"));
    $src2->compositeImage($src, \Imagick::COMPOSITE_ATOP , 270, 250);
    $src2->writeImage(public_path("image/output1-crni-ram-b1.png"));

    echo '<img src="/image/output1-crni-ram-b1.png" alt="" />'; 
  }


  public function uploadMockup33(Request $request){
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

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(550, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
    $src2 = new \Imagick(public_path("\site-images\Poster---Crni-Ram---B2.jpg"));
  
    
    
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
    
     $process5 = new Process('magick convert ^
    '.$path.'\site-images\Poster---Crni-Ram---B2.jpg ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-crni-ram-b2.png
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
     echo '<img src="\image\ms_light_map-crni-ram-b2.png">';
    
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask-crni-ram-b2.png
      ');
  
     
    
    $process6->run();
    if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
    }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask-crni-ram-b2.png">';
    
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-crni-ram-b2.png ^
     -geometry -680-230 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask-crni-ram-b2.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo-crni-ram-b2.png
     ');
    
    $process7->run();
    if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
    }
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo-crni-ram-b2.png">';
    
    $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
    $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
    $src = new \Imagick(public_path("\image\ms_light_map_logo-crni-ram-b2.png"));
    $src2->compositeImage($src, \Imagick::COMPOSITE_ATOP , 680, 230);
    $src2->writeImage(public_path("image/output1-crni-ram-b2.png"));

    echo '<img src="/image/output1-crni-ram-b2.png" alt="" />'; 
  }


  public function uploadMockup34(Request $request){
      
      
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

    
 
    

$src1 = new \Imagick(public_path("design". $imageName1));
/* $src1->resizeImage(530, null,\Imagick::FILTER_LANCZOS,1); 
$src1->writeImage(public_path("design". $imageName1)); */
$src2 = new \Imagick(public_path("\site-images\Huawei-P20-Bezpozadinecopy.png"));



$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 

$process5 = new Process('magick convert ^
'.$path.'\site-images\Huawei-P20-Bezpozadinecopy.png ^
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
-geometry -150-140 ^
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
$src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 150, 140);
$src2->writeImage(public_path("image/output1.png"));
$process5 = new Process('magick  convert '.$path.'\image\output1.png 
-flatten  '.$path.'\image\out.png 
');
 $process5->run();
    if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
    }
     echo $process5->getOutput();
     echo '<img src="\design'.$imageName1.'">';
     echo '<img src="\image\out.png">'; 

     $process10 = new Process('magick  convert '.$path.'\site-images\Huawei-P20-Bezpozadinecopy.png -background "rgb(0,0,0)" 
     -flatten  '.$path.'\image\Huawei-P20-Crna.png
    ');
       $process10->run();
          if (!$process10->isSuccessful()) {
           throw new ProcessFailedException($process10);
          }
           echo $process10->getOutput();
           echo '<img src="\image\Huawei-P20-Crna.png">'; 


/* $src1 = new \Imagick(public_path("\image\ms_light_map_logo_phone.png")); */


$process8 = new Process('magick convert ^
'.$path.'\image\Huawei-P20-Crna.png ^
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
}


  public function uploadMockup35(Request $request){
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

    
    

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(80, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $Y = $src1->getImageHeight()/2;
     $process = new Process('magick convert -size 1000x1000 tile:'.$path.'/design'.$imageName1. ' ' .$path.'/image/Tiles.png
');

$process->run();
if (!$process->isSuccessful()) {
throw new ProcessFailedException($process);
}
echo $process->getOutput();
echo '<img src="\image\Tiles.png">';



$src2 = new \Imagick(public_path("\site-images\Tapete-Thumbnail-mockup.png"));
$src2->resizeImage(1000, null,\Imagick::FILTER_LANCZOS,1); 
$src2->writeImage(public_path("\site-images\Tapete-Thumbnail-mockup.png"));



$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 

$process5 = new Process('magick convert ^
'.$path.'\site-images\Tapete-Thumbnail-mockup.png ^
-channel A -blur 0x8
-compose hardlight
'.$path.'\image\ms_light_map-tapeta.png
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
echo '<img src="\image\ms_light_map-tapeta.png">';

$process6 = new Process('magick convert ^
'.$path.'/image/Tiles.png ^

'.$path.'\image\ms_logo_displace_mask_tapeta.png
');



$process6->run();
if (!$process6->isSuccessful()) {
throw new ProcessFailedException($process6);
}
echo $process6->getOutput();
echo '<img src="\image\ms_logo_displace_mask_tapeta.png">';

$process7 = new Process('magick convert ^
'.$path.'\image/Tiles.png ^
'.$path.'\image\ms_light_map-tapeta.png ^
-geometry -300-0 ^
-compose Multiply -composite ^
'.$path.'\image\ms_logo_displace_mask_tapeta.png ^
-compose CopyOpacity -composite ^
'.$path.'\image\ms_light_map_logo_tapeta.png
');

$process7->run();
if (!$process7->isSuccessful()) {
throw new ProcessFailedException($process7);
}
echo $process7->getOutput();
echo '<img src="\image\ms_light_map_logo_tapeta.png">';

$src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
$src = new \Imagick(public_path("\image\ms_light_map_logo_tapeta.png"));
$src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 300, 0);
$src2->writeImage(public_path("image/output1.png"));
$process5 = new Process('magick  convert '.$path.'\image\output1.png 
-flatten  '.$path.'\image\out.png 
');
 $process5->run();
    if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
    }
     echo $process5->getOutput();
     echo '<img src="\image\out.png">'; 

    /*  $process10 = new Process('magick  convert '.$path.'\site-images\Huawei-P20-Bezpozadinecopy.png -background "rgb(0,0,0)" 
     -flatten  '.$path.'\image\Huawei-P20-Crna.png
    ');
       $process10->run();
          if (!$process10->isSuccessful()) {
           throw new ProcessFailedException($process10);
          }
           echo $process10->getOutput();
           echo '<img src="\image\Huawei-P20-Crna.png">'; 


 $src1 = new \Imagick(public_path("\image\ms_light_map_logo_phone.png")); 


$process8 = new Process('magick convert ^
'.$path.'\image\Huawei-P20-Crna.png ^
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
echo '<img src="\image\ms_product_phone1.png">'; */
  }

  public function uploadMockup36(Request $request){
    
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

    
    

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(100, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));

     $process = new Process('magick convert -size 1000x1000 tile:'.$path.'/design'.$imageName1. ' ' .$path.'/image/Tiles.png
');

$process->run();
if (!$process->isSuccessful()) {
throw new ProcessFailedException($process);
}
echo $process->getOutput();
echo '<img src="\image\Tiles.png">';



$src2 = new \Imagick(public_path("\site-images\Tapete-Thumbnail-mockup-2.png"));
$src2->resizeImage(1000, null,\Imagick::FILTER_LANCZOS,1); 
$src2->writeImage(public_path("\site-images\Tapete-Thumbnail-mockup-2.png"));



$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 

$process5 = new Process('magick convert ^
'.$path.'\site-images\Tapete-Thumbnail-mockup-2.png ^
-channel A -blur 0x8
-compose hardlight
'.$path.'\image\ms_light_map-tapeta-2.png
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
echo '<img src="\image\ms_light_map-tapeta-2.png">';

$process6 = new Process('magick convert ^
'.$path.'/image/Tiles.png ^

'.$path.'\image\ms_logo_displace_mask_tapeta-2.png
');



$process6->run();
if (!$process6->isSuccessful()) {
throw new ProcessFailedException($process6);
}
echo $process6->getOutput();
echo '<img src="\image\ms_logo_displace_mask_tapeta-2.png">';

$process7 = new Process('magick convert ^
'.$path.'\image/Tiles.png ^
'.$path.'\image\ms_light_map-tapeta-2.png ^
-geometry -0-0 ^
-compose Multiply -composite ^
'.$path.'\image\ms_logo_displace_mask_tapeta-2.png ^
-compose CopyOpacity -composite ^
'.$path.'\image\ms_light_map_logo_tapeta-2.png
');

$process7->run();
if (!$process7->isSuccessful()) {
throw new ProcessFailedException($process7);
}
echo $process7->getOutput();
echo '<img src="\image\ms_light_map_logo_tapeta-2.png">';

$src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
$src = new \Imagick(public_path("\image\ms_light_map_logo_tapeta-2.png"));
$src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 0, 0);
$src2->writeImage(public_path("image/output1.png"));
$process5 = new Process('magick  convert '.$path.'\image\output1.png 
-flatten  '.$path.'\image\out.png 
');
 $process5->run();
    if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
    }
     echo $process5->getOutput();
     echo '<img src="\image\out.png">'; 
  }


  public function uploadMockup37(Request $request){

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

    
    

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(80, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
    $Y = $src1->getImageHeight()/2;
     $process = new Process('magick convert '.$path.'/design'.$imageName1.' -roll +0+'. $Y . ' ' .$path.'/image/_orange_270_r.png
');

$process->run();
if (!$process->isSuccessful()) {
throw new ProcessFailedException($process);
}
echo $process->getOutput();
echo '<img src="\image\_orange_270_r.png">';

$process1 = new Process('magick  montage  '.$path.'/design'.$imageName1.' +clone +clone +clone -tile x4 
-geometry +0+0  '.$path.'/image/_1col.png
');

$process1->run();
if (!$process1->isSuccessful()) {
throw new ProcessFailedException($process1);
}
echo $process1->getOutput();
echo '<img src="\image\_1col.png">';

$process2 = new Process('magick  montage  '.$path.'/image/_orange_270_r.png +clone +clone +clone
 -tile x4 -geometry +0+0  '.$path.'/image/_2col.png


');

$process2->run();
if (!$process2->isSuccessful()) {
throw new ProcessFailedException($process2);
}
echo $process2->getOutput();
echo '<img src="\image\_2col.png">';

$process3 = new Process('magick  montage -geometry +0+0 '.$path.'/image/_1col.png '.$path.'/image/_2col.png '.$path.'/image/_2cols.png
');

$process3->run();
if (!$process3->isSuccessful()) {
throw new ProcessFailedException($process3);
}
echo $process3->getOutput();
echo '<img src="\image\_2cols.png">';

$process4 = new Process('magick  convert '.$path.'/image/_2cols.png -write mpr:tile  +delete -size 1920x1080 tile:mpr:tile '.$path.'/image/_wallpap.png
');

$process4->run();
if (!$process4->isSuccessful()) {
throw new ProcessFailedException($process4);
}
echo $process3->getOutput();
echo '<img src="\image\_wallpap.png">';

$src2 = new \Imagick(public_path("\site-images\Tapete-Thumbnail-mockup-2.png"));
$src2->resizeImage(1000, null,\Imagick::FILTER_LANCZOS,1); 
$src2->writeImage(public_path("\site-images\Tapete-Thumbnail-mockup-2.png"));



$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 

$process5 = new Process('magick convert ^
'.$path.'\site-images\Tapete-Thumbnail-mockup-2.png ^
-channel A -blur 0x8
-compose hardlight
'.$path.'\image\ms_light_map-tapeta-2.png
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
echo '<img src="\image\ms_light_map-tapeta-2.png">';

$process6 = new Process('magick convert ^
'.$path.'/image/_wallpap.png ^

'.$path.'\image\ms_logo_displace_mask_tapeta-2.png
');



$process6->run();
if (!$process6->isSuccessful()) {
throw new ProcessFailedException($process6);
}
echo $process6->getOutput();
echo '<img src="\image\ms_logo_displace_mask_tapeta-2.png">';

$process7 = new Process('magick convert ^
'.$path.'\image/_wallpap.png ^
'.$path.'\image\ms_light_map-tapeta-2.png ^
-geometry -0-0 ^

'.$path.'\image\ms_logo_displace_mask_tapeta-2.png ^

');

$process7->run();
if (!$process7->isSuccessful()) {
throw new ProcessFailedException($process7);
}
echo $process7->getOutput();
echo '<img src="\image\ms_light_map_logo_tapeta-2.png">';

$src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
$src = new \Imagick(public_path("\image\ms_light_map_logo_tapeta-2.png"));
$src3 = new \Imagick(public_path("image/_wallpap.png"));
$src2->compositeImage($src3, \Imagick::COMPOSITE_DSTOVER, 0, 0);
$src2->writeImage(public_path("image/output1.png"));
$process5 = new Process('magick  convert '.$path.'\image\output1.png 
-flatten  '.$path.'\image\out.png 
');
 $process5->run();
    if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
    }
     echo $process5->getOutput();
     echo '<img src="\image\out.png">'; 

  }

  public function uploadMockup38(Request $request){

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

    
    

     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(80, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
    $Y = $src1->getImageHeight()/2;
     $process = new Process('magick convert '.$path.'/design'.$imageName1.' -roll +0+'. $Y . ' ' .$path.'/image/_orange_270_r.png
');

$process->run();
if (!$process->isSuccessful()) {
throw new ProcessFailedException($process);
}
echo $process->getOutput();
echo '<img src="\image\_orange_270_r.png">';

$process1 = new Process('magick  montage  '.$path.'/design'.$imageName1.' +clone +clone +clone -tile x4 
-geometry +0+0  '.$path.'/image/_1col.png
');

$process1->run();
if (!$process1->isSuccessful()) {
throw new ProcessFailedException($process1);
}
echo $process1->getOutput();
echo '<img src="\image\_1col.png">';

$process2 = new Process('magick  montage  '.$path.'/image/_orange_270_r.png +clone +clone +clone
 -tile x4 -geometry +0+0  '.$path.'/image/_2col.png


');

$process2->run();
if (!$process2->isSuccessful()) {
throw new ProcessFailedException($process2);
}
echo $process2->getOutput();
echo '<img src="\image\_2col.png">';

$process3 = new Process('magick  montage -geometry +0+0 '.$path.'/image/_1col.png '.$path.'/image/_2col.png '.$path.'/image/_2cols.png
');

$process3->run();
if (!$process3->isSuccessful()) {
throw new ProcessFailedException($process3);
}
echo $process3->getOutput();
echo '<img src="\image\_2cols.png">';

$process4 = new Process('magick  convert '.$path.'/image/_2cols.png -write mpr:tile  +delete -size 1920x1080 tile:mpr:tile '.$path.'/image/_wallpap.png
');

$process4->run();
if (!$process4->isSuccessful()) {
throw new ProcessFailedException($process4);
}
echo $process3->getOutput();
echo '<img src="\image\_wallpap.png">';

$src2 = new \Imagick(public_path("\site-images\Tapete-Thumbnail-mockup.png"));
$src2->resizeImage(1000, null,\Imagick::FILTER_LANCZOS,1); 
$src2->writeImage(public_path("\site-images\Tapete-Thumbnail-mockup.png"));



$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 

$process5 = new Process('magick convert ^
'.$path.'\site-images\Tapete-Thumbnail-mockup.png ^
-channel A -blur 0x8
-compose hardlight
'.$path.'\image\ms_light_map-tapeta-2.png
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
echo '<img src="\image\ms_light_map-tapeta-2.png">';

$process6 = new Process('magick convert ^
'.$path.'/image/_wallpap.png ^

'.$path.'\image\ms_logo_displace_mask_tapeta-2.png
');



$process6->run();
if (!$process6->isSuccessful()) {
throw new ProcessFailedException($process6);
}
echo $process6->getOutput();
echo '<img src="\image\ms_logo_displace_mask_tapeta-2.png">';

$process7 = new Process('magick convert ^
'.$path.'\image/_wallpap.png ^
'.$path.'\image\ms_light_map-tapeta-2.png ^
-geometry -0-0 ^

'.$path.'\image\ms_logo_displace_mask_tapeta-2.png ^

');

$process7->run();
if (!$process7->isSuccessful()) {
throw new ProcessFailedException($process7);
}
echo $process7->getOutput();
echo '<img src="\image\ms_light_map_logo_tapeta-2.png">';

$src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
$src = new \Imagick(public_path("\image\ms_light_map_logo_tapeta-2.png"));
$src3 = new \Imagick(public_path("image/_wallpap.png"));
$src2->compositeImage($src3, \Imagick::COMPOSITE_DSTOVER, 0, 0);
$src2->writeImage(public_path("image/output1.png"));
$process5 = new Process('magick  convert '.$path.'\image\output1.png 
-flatten  '.$path.'\image\out.png 
');
 $process5->run();
    if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
    }
     echo $process5->getOutput();
     echo '<img src="\image\out.png">'; 

  }

  public function uploadMockup39(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $imageName1 = "/" .  $image; 

     $path = public_path();


     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(700, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $src2 = new \Imagick(public_path("\site-images\Canvas-mockup-thumbnail.png"));
     $src2->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src2->writeImage(public_path("\site-images\Canvas-mockup-thumbnail.png"));
     
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
     
     $process5 = new Process('magick convert ^
     '.$path.'\site-images\Canvas-mockup-thumbnail.png ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-canvas.png
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
     echo '<img src="\image\ms_light_map-canvas.png">';
     
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask_canvas.png
     ');
     
     
     
     $process6->run();
     if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
     }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask_canvas.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-canvas.png ^
     -geometry -390-450 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask_canvas.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo_canvas.png
     ');
     
     $process7->run();
     if (!$process7->isSuccessful()) {
     throw new ProcessFailedException($process7);
     }
     echo $process7->getOutput();
     echo '<img src="\image\ms_light_map_logo_canvas.png">';


     $src2->compositeImage($src1, \Imagick::COMPOSITE_DSTOVER ,390,450);
     $src2->writeImage(public_path("image\image-canvas.png"));
     echo '<img src="\image\image-canvas.png">';
     $src4 = new \Imagick(public_path("site-images/Textura-Canvas-mockup.png"));
     $src4->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src4->writeImage(public_path("\site-images/Textura-Canvas-mockup.png"));
      $src3 = new \Imagick(public_path("image/image-canvas.png"));
      $src3->compositeImage($src4, \Imagick::COMPOSITE_MULTIPLY,0,0);
      $src3->writeImage(public_path("image\multiply_canvas.png"));

    echo '<img src="\image\multiply_canvas.png">'; 
     dd();
     $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
     $src = new \Imagick(public_path("\image\ms_light_map_logo_canvas.png"));
     $src2->compositeImage($src, \Imagick::COMPOSITE_COLORIZE , 340, 620);
     $src2->writeImage(public_path("image/output1.png"));
     $process5 = new Process('magick  convert '.$path.'\image\output1.png 
     -flatten  '.$path.'\image\out_canvas.png 
     ');
      $process5->run();
         if (!$process5->isSuccessful()) {
          throw new ProcessFailedException($process5);
         }
          echo $process5->getOutput();
          echo '<img src="\image\out_canvas.png">'; 

  }

  public function uploadMockup40(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
   // dd($extension);
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

     $process0 = new Process('magick convert '.$path.'\site-images\Torbacrnaruckamanjam.jpg
     -resize 1200x2000
     '.$path.'\site-images\Torbacrnaruckamanjam.jpg
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 

      $process = new Process('magick convert   '.$path.'\site-images\Torbacrnaruckamanjam.jpg[403x422+440+501] 
     -colorspace gray 
     -blur 10x250 
     -auto-level
     '.$path.'\image\displace_map.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map.png">'; 

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 400x400
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 403x422 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';


     list($width, $height) = getimagesize($path.'\image\ms_temp.png');

   
     $X = 440 + (403-$width)/2;
     $Y = 501 +  (422-$height)/2;
    

      $process3 = new Process('magick convert 
     '.$path.'\site-images\Torbacrnaruckamanjam.jpg[403x422+410+451] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map_girl_white_regular.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map_girl_white_regular.png">'; 
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map_girl_white_regular.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

     
      $process5 = new Process('magick convert ^
     '.$path.'\site-images\Torbacrnaruckamanjam.jpg[403x422+410+451] ^
     -colorspace gray -auto-level ^
     -blur 0x4 ^
     -contrast-stretch 0,30%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map_girl_white_regular.png
      ');

/*         Makao sam komandu -separate proces 5 */

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map_girl_white_regular.png">'; 
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map_girl_white_regular.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    
   
   /*  -geometry +984+1101 */

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\Torbacrvenaruckamanja.jpg ^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.'
     -compose over    -composite ^
     -depth 24 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';


    $process9 = new Process('magick convert ^
    '.$path.'\site-images\proba.jpg ^
    '.$path.'\image\ms_light_map_logo.png ^
    -geometry +'.$X.'+'.$Y.'
    -compose over -composite ^
    -depth 16 ^
    '.$path.'\image\ms_product-crvena.png
    ');

$process9->run();
if (!$process9->isSuccessful()) {
   throw new ProcessFailedException($process9);
}
   echo $process9->getOutput();
   echo '<img src="\image\ms_product-crvena.png">';
  }


  public function uploadMockup41(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
   // dd($extension);
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

    /*  $process0 = new Process(' convert -version
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 
      echo $process0->getOutput();

      dd(); */
     $process0 = new Process('magick convert '.$path.'\site-images\Torbaobicnasalinijom.jpg
     -resize 1200x2000
     '.$path.'\site-images\Torbaobicnasalinijom.jpg
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 

      $processa = new Process('magick convert '.$path.'\site-images\Torbaobicnasalinijom.jpg
      -resize 1200x2000
      '.$path.'\site-images\Torbaobicnasalinijom.jpg
       ');
       $processa->run();
       if (!$processa->isSuccessful()) {
           throw new ProcessFailedException($processa);
       } 

      $process = new Process('magick convert   '.$path.'\site-images\Torbaobicnasalinijom.jpg[403x422+390+481] 
     -colorspace gray 
     -blur 10x250 
     -auto-level
     '.$path.'\image\displace_map.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map.png">'; 

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 400x400
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 403x422 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';


     list($width, $height) = getimagesize($path.'\image\ms_temp.png');

   
     $X = 390 + (403-$width)/2;
     $Y = 481 +  (422-$height)/2;
    

      $process3 = new Process('magick convert 
     '.$path.'\site-images\Torbaobicnasalinijom.jpg[403x422+390+481] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map_girl_white_regular.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map_girl_white_regular.png">'; 
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map_girl_white_regular.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

     
      $process5 = new Process('magick convert ^
     '.$path.'\site-images\Torbaobicnasalinijom.jpg[403x422+390+481] ^
     -colorspace gray -auto-level ^
     -blur 0x4 ^
     -contrast-stretch 0,30%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map_girl_white_regular.png
      ');

/*         Makao sam komandu -separate proces 5 */

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map_girl_white_regular.png">'; 
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map_girl_white_regular.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    
   
   /*  -geometry +984+1101 */

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\Torbaobicnasalinijom.jpg^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.'
     -compose over -composite ^
     -depth 16 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';
  }


  public function uploadMockup42(Request $request){

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

    
 
    

$src1 = new \Imagick(public_path("design". $imageName1));
$src1->resizeImage(500, null,\Imagick::FILTER_LANCZOS,1); 
$src1->writeImage(public_path("design". $imageName1));
$src2 = new \Imagick(public_path("\site-images\SatCrni.png"));
$src3 = new \Imagick(public_path("\site-images\SatBijeli.png"));


$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 

$process5 = new Process('magick convert ^
'.$path.'\site-images\SatCrni.png ^
-channel A -blur 0x8
-compose hardlight
'.$path.'\image\ms_light_map-clock.png
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
echo '<img src="\image\ms_light_map-clock.png">';

$process6 = new Process('magick convert ^
'.$path.'\design'. $imageName1. ' ^
-channel matte -separate ^
'.$path.'\image\ms_logo_displace_mask_clock.png
');



$process6->run();
if (!$process6->isSuccessful()) {
throw new ProcessFailedException($process6);
}
echo $process6->getOutput();
echo '<img src="\image\ms_logo_displace_mask_clock.png">';

$process7 = new Process('magick convert ^
'.$path.'\design'. $imageName1. ' ^
'.$path.'\image\ms_light_map-clock.png ^
-geometry -310-240 ^
-compose Multiply -composite ^
'.$path.'\image\ms_logo_displace_mask_clock.png ^
-compose CopyOpacity -composite ^
'.$path.'\image\ms_light_map_logo_clock.png
');

$process7->run();
if (!$process7->isSuccessful()) {
throw new ProcessFailedException($process7);
}
echo $process7->getOutput();
echo '<img src="\image\ms_light_map_logo_clock.png">';

$src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
$src = new \Imagick(public_path("\image\ms_light_map_logo_clock.png"));
$src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 310, 240);
$src2->writeImage(public_path("image/clock.png"));
$process5 = new Process('magick  convert '.$path.'\image\clock.png -background "rgb(0,0,0)" 
-flatten  '.$path.'\image\out.png 
');
 $process5->run();
    if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
    }
     echo $process5->getOutput();
     echo '<img src="\image\out.png">'; 

   /*   $process9 = new Process('magick  convert '.$path.'\site-images\SatCrni.png -background "rgb(0,0,0)" 
-flatten  '.$path.'\site-images\CrniSatSite.png 
');
 $process9->run();
    if (!$process9->isSuccessful()) {
     throw new ProcessFailedException($process9);
    }
     echo $process9->getOutput();
     echo '<img src="\site-images\CrniSatSite.png">';  */

     $src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 310, 240);
     $src2->writeImage(public_path("image/output1.png"));
     $process5 = new Process('magick  convert '.$path.'\site-images\SatBijeli.png  
     -flatten  '.$path.'\image\out1.png 
     ');
      $process5->run();
         if (!$process5->isSuccessful()) {
          throw new ProcessFailedException($process5);
         }
          echo $process5->getOutput();
        $src3->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 310, 240);
        $src3->writeImage(public_path("image/output3.png"));
        echo '<img src="\image\output3.png">'; 
dd();
    
  }

  public function uploadMockup43(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $imageName1 = "/" .  $image; 

     $path = public_path();


     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(400, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $src2 = new \Imagick(public_path("\site-images\podmetackvadratasti.jpg"));
   /*   $src2->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src2->writeImage(public_path("\site-images\podmetackvadratasti.jpg")); */
     
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
     
     $process5 = new Process('magick convert ^
     '.$path.'\site-images\podmetackvadratasti.jpg ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-podmetac.jpg
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
     echo '<img src="\image\ms_light_map-podmetac.jpg">';
     
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask_podmetac.png
     ');
     
     
     
     $process6->run();
     if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
     }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask_podmetac.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-podmetac.jpg ^
     -geometry -725-300 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask_podmetac.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo_podmetac.png
     ');
     
     $process7->run();
     if (!$process7->isSuccessful()) {
     throw new ProcessFailedException($process7);
     }
     echo $process7->getOutput();
     echo '<img src="\image\ms_light_map_logo_podmetac.png">';


     $src2->compositeImage($src1, \Imagick::COMPOSITE_DEFAULT  ,725,300);
     $src2->writeImage(public_path("image\image-podmetac.png"));
     echo '<img src="\image\image-podmetac.png">';
     $src4 = new \Imagick(public_path("site-images/podmetackvadratasti.jpg"));
   /*   $src4->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1);  
     $src4->writeImage(public_path("\site-images/podmetackvadratasti.jpg"));*/
      $src3 = new \Imagick(public_path("image/image-podmetac.png"));
      $src3->compositeImage($src4, \Imagick::COMPOSITE_MULTIPLY,0,0);
      $src3->writeImage(public_path("image\multiply_podmetac.png"));

    echo '<img src="\image\multiply_podmetac.png">'; 
     dd();
     $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
     $src = new \Imagick(public_path("\image\ms_light_map_logo_canvas.png"));
     $src2->compositeImage($src, \Imagick::COMPOSITE_COLORIZE , 340, 620);
     $src2->writeImage(public_path("image/output1.png"));
     $process5 = new Process('magick  convert '.$path.'\image\output1.png 
     -flatten  '.$path.'\image\out_canvas.png 
     ');
      $process5->run();
         if (!$process5->isSuccessful()) {
          throw new ProcessFailedException($process5);
         }
          echo $process5->getOutput();
          echo '<img src="\image\out_canvas.png">'; 

  }


  public function uploadMockup44(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $imageName1 = "/" .  $image; 

     $path = public_path();


     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(300, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $src2 = new \Imagick(public_path("\site-images\podmetacokrugli.jpg"));
    /*  $src2->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src2->writeImage(public_path("\site-images\podmetacokrugli.jpg")); */
     
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
     
     $process5 = new Process('magick convert ^
     '.$path.'\site-images\podmetacokrugli.jpg ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-podmetac.jpg
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
     echo '<img src="\image\ms_light_map-podmetac.jpg">';
     
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask_podmetac.png
     ');
     
     
     
     $process6->run();
     if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
     }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask_podmetac.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-podmetac.jpg ^
     -geometry -740-570 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask_podmetac.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo_podmetac.png
     ');
     
     $process7->run();
     if (!$process7->isSuccessful()) {
     throw new ProcessFailedException($process7);
     }
     echo $process7->getOutput();
     echo '<img src="\image\ms_light_map_logo_podmetac.png">';


     $src2->compositeImage($src1, \Imagick::COMPOSITE_DEFAULT  ,740,570);
     $src2->writeImage(public_path("image\image-podmetac.png"));
     echo '<img src="\image\image-podmetac.png">';
     $src4 = new \Imagick(public_path("site-images/podmetacokrugli.jpg"));
    /*  $src4->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src4->writeImage(public_path("\site-images/podmetacokrugli.jpg")); */
      $src3 = new \Imagick(public_path("image/image-podmetac.png"));
      $src3->compositeImage($src4, \Imagick::COMPOSITE_MULTIPLY,0,0);
      $src3->writeImage(public_path("image\multiply_podmetac.png"));

    echo '<img src="\image\multiply_podmetac.png">'; 
     dd();
     $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
     $src = new \Imagick(public_path("\image\ms_light_map_logo_canvas.png"));
     $src2->compositeImage($src, \Imagick::COMPOSITE_COLORIZE , 340, 620);
     $src2->writeImage(public_path("image/output1.png"));
     $process5 = new Process('magick  convert '.$path.'\image\output1.png 
     -flatten  '.$path.'\image\out_canvas.png 
     ');
      $process5->run();
         if (!$process5->isSuccessful()) {
          throw new ProcessFailedException($process5);
         }
          echo $process5->getOutput();
          echo '<img src="\image\out_canvas.png">'; 
  }

  public function uploadMockup45(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
   // dd($extension);
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

     $process0 = new Process('magick convert '.$path.'\site-images\PapirnakesaDina.jpg
     -resize 1200x2000
     '.$path.'\site-images\PapirnakesaDina.jpg
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 

      $processa = new Process('magick convert '.$path.'\site-images\PapirnakesaDinacrna.jpg
      -resize 1200x2000
      '.$path.'\site-images\PapirnakesaDinacrna.jpg
       ');
       $processa->run();
       if (!$processa->isSuccessful()) {
           throw new ProcessFailedException($processa);
       } 

      $process = new Process('magick convert   '.$path.'\site-images\PapirnakesaDina.jpg[303x322+450+311] 
     -colorspace gray 
     -blur 10x250 
     -auto-level
     '.$path.'\image\displace_map.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map.png">'; 

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 300x300
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 303x322 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';


     list($width, $height) = getimagesize($path.'\image\ms_temp.png');

    
     $X = 450 + (303-$width)/2;
     $Y = 311 +  (322-$height)/2;
    

      $process3 = new Process('magick convert 
     '.$path.'\site-images\PapirnakesaDina.jpg[303x322+450+311] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map_girl_white_regular.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map_girl_white_regular.png">'; 
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map_girl_white_regular.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

     
      $process5 = new Process('magick convert ^
     '.$path.'\site-images\PapirnakesaDina.jpg[303x322+450+311] ^
     -colorspace gray -auto-level ^
     -blur 0x4 ^
     -contrast-stretch 0,30%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map_girl_white_regular.png
      ');

/*         Makao sam komandu -separate proces 5 */

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map_girl_white_regular.png">'; 
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map_girl_white_regular.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    
   
   /*  -geometry +984+1101 */

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\PapirnakesaDina.jpg ^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.'
     -compose over    -composite ^
     -depth 24 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';


    $process9 = new Process('magick convert ^
    '.$path.'\site-images\PapirnakesaDinacrna.jpg ^
    '.$path.'\image\ms_light_map_logo.png ^
    -geometry +'.$X.'+'.$Y.'
    -compose over -composite ^
    -depth 16 ^
    '.$path.'\image\ms_product-crna.png
    ');

$process9->run();
if (!$process9->isSuccessful()) {
   throw new ProcessFailedException($process9);
}
   echo $process9->getOutput();
   echo '<img src="\image\ms_product-crna.png">';
  }


  public function uploadMockup46(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
   // dd($extension);
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

    /*  $process0 = new Process(' convert -version
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 
      echo $process0->getOutput();

      dd(); */
     $process0 = new Process('magick convert '.$path.'\site-images\PapirnakesaDinaHo2.jpg
     -resize 1200x2000
     '.$path.'\site-images\PapirnakesaDinaHo2.jpg
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 

      $processa = new Process('magick convert '.$path.'\site-images\PapirnakesaDinaHocrn.jpg
      -resize 1200x2000
      '.$path.'\site-images\PapirnakesaDinaHocrn.jpg
       ');
       $processa->run();
       if (!$processa->isSuccessful()) {
           throw new ProcessFailedException($processa);
       } 

      $process = new Process('magick convert   '.$path.'\site-images\PapirnakesaDinaHo2.jpg[303x322+430+431] 
     -colorspace gray 
     -blur 10x250 
     -auto-level
     '.$path.'\image\displace_map.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map.png">'; 

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 300x300
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 303x322 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';


     list($width, $height) = getimagesize($path.'\image\ms_temp.png');

    
     $X = 430 + (303-$width)/2;
     $Y = 431 +  (322-$height)/2;
    

      $process3 = new Process('magick convert 
     '.$path.'\site-images\PapirnakesaDinaHo2.jpg[303x322+430+431] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map_girl_white_regular.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map_girl_white_regular.png">'; 
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map_girl_white_regular.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

     
      $process5 = new Process('magick convert ^
     '.$path.'\site-images\PapirnakesaDinaHo2.jpg[303x322+430+431] ^
     -colorspace gray -auto-level ^
     -blur 0x4 ^
     -contrast-stretch 0,30%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map_girl_white_regular.png
      ');

/*         Makao sam komandu -separate proces 5 */

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map_girl_white_regular.png">'; 
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map_girl_white_regular.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">';
    
   
   /*  -geometry +984+1101 */

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\PapirnakesaDinaHo2.jpg ^
     '.$path.'\image\ms_light_map_logo.png ^
     -geometry +'.$X.'+'.$Y.'
     -compose over    -composite ^
     -depth 24 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';


    $process9 = new Process('magick convert ^
    '.$path.'\site-images\PapirnakesaDinaHocrn.jpg ^
    '.$path.'\image\ms_light_map_logo.png ^
    -geometry +'.$X.'+'.$Y.'
    -compose over -composite ^
    -depth 16 ^
    '.$path.'\image\ms_product-crna.png
    ');

$process9->run();
if (!$process9->isSuccessful()) {
   throw new ProcessFailedException($process9);
}
   echo $process9->getOutput();
   echo '<img src="\image\ms_product-crna.png">';
  }


  public function uploadMockup47(Request $request){

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

    
 
    
     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(300, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $src2 = new \Imagick(public_path("\site-images\Notes.png"));
    
     
     
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
     
     $process5 = new Process('magick convert ^
     '.$path.'\site-images\Notescrni.png ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-notes.png
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
     echo '<img src="\image\ms_light_map-notes.png">';
     
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask_notes.png
     ');
     
     
     
     $process6->run();
     if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
     }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask_notes.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-notes.png ^
     -geometry -430-420 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask_notes.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo_notes.png
     ');
     
     $process7->run();
     if (!$process7->isSuccessful()) {
     throw new ProcessFailedException($process7);
     }
     echo $process7->getOutput();
     echo '<img src="\image\ms_light_map_logo_notes.png">';
     
     $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
     $src = new \Imagick(public_path("\image\ms_light_map_logo_notes.png"));
     $src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 430, 420);
     $src2->writeImage(public_path("image/notes1.png"));
     $process5 = new Process('magick  convert '.$path.'\image\notes1.png  
     -flatten  '.$path.'\image\notes.png 
     ');
      $process5->run();
         if (!$process5->isSuccessful()) {
          throw new ProcessFailedException($process5);
         }
          echo $process5->getOutput();
          echo '<img src="\image\notes.png">'; 
     
      /*      $process9 = new Process('magick  convert '.$path.'\site-images\Notescrni.png -background "rgb(0,0,0)" 
     -flatten  '.$path.'\site-images\Notescrnislika.png 
     ');
      $process9->run();
         if (!$process9->isSuccessful()) {
          throw new ProcessFailedException($process9);
         }
          echo $process9->getOutput();
          echo '<img src="\site-images\Notescrnislika.png">'; */  
     
          $src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 430, 420);
          $src2->writeImage(public_path("image/output1.png"));
          $process5 = new Process('magick  convert '.$path.'\site-images\Notes.png  
          -flatten  '.$path.'\image\out1.png 
          ');
           $process5->run();
              if (!$process5->isSuccessful()) {
               throw new ProcessFailedException($process5);
              }
               echo $process5->getOutput();
               $src3 = new \Imagick(public_path("\site-images\Notescrni.png"));
             $src3->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 430, 420);
             $src3->setImageBackgroundColor('#fad888');
             $src3->writeImage(public_path("image/notes3.png"));
             $process9 = new Process('magick  convert '.$path.'\image\notes3.png -background "rgb(0,0,0)" 
     -flatten  '.$path.'\image\notes3.png 
     ');
      $process9->run();
         if (!$process9->isSuccessful()) {
          throw new ProcessFailedException($process9);
         }
          echo $process9->getOutput();
    
             echo '<img src="\image\notes3.png">'; 
  }


  public function uploadMockup48(Request $request){
      
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

    
 
    
     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(350, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $src2 = new \Imagick(public_path("\site-images\Karticni.png"));
    
     
     
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
     
     $process5 = new Process('magick convert ^
     '.$path.'\site-images\Karticni.png ^
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
     -geometry -430-270 ^
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
     $src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 430, 270);
     $src2->writeImage(public_path("image/output1.png"));
     $process5 = new Process('magick  convert '.$path.'\image\output1.png  
     -flatten  '.$path.'\image\out.png 
     ');
      $process5->run();
         if (!$process5->isSuccessful()) {
          throw new ProcessFailedException($process5);
         }
          echo $process5->getOutput();
          echo '<img src="\image\out.png">'; 
  }


  public function uploadMockup49(Request $request){
      
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

    
 
    
     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(350, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $src2 = new \Imagick(public_path("\site-images\Okrugli.png"));
    
     
     
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
     
     $process5 = new Process('magick convert ^
     '.$path.'\site-images\Okrugli.png ^
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
     -geometry -425-280 ^
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
     $src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 425, 280);
     $src2->writeImage(public_path("image/output1.png"));
     $process5 = new Process('magick  convert '.$path.'\image\output1.png  
     -flatten  '.$path.'\image\out.png 
     ');
      $process5->run();
         if (!$process5->isSuccessful()) {
          throw new ProcessFailedException($process5);
         }
          echo $process5->getOutput();
          echo '<img src="\image\out.png">'; 
  }

  public function uploadMockup50(Request $request){
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

    
 
    
     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(350, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $src2 = new \Imagick(public_path("\site-images\Puzle.png"));
    
     
     
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
     
     $process5 = new Process('magick convert ^
     '.$path.'\site-images\Puzle.png ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-puzle.png
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
     echo '<img src="\image\ms_light_map-puzle.png">';
     
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask_puzle.png
     ');
     
     
     
     $process6->run();
     if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
     }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask_puzle.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-puzle.png ^
     -geometry -205-100 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask_puzle.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo_puzle.png
     ');
     
     $process7->run();
     if (!$process7->isSuccessful()) {
     throw new ProcessFailedException($process7);
     }
     echo $process7->getOutput();
     echo '<img src="\image\ms_light_map_logo_puzle.png">';
     
     $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
     $src = new \Imagick(public_path("\image\ms_light_map_logo_puzle.png"));
     $src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 205, 100);
     $src2->writeImage(public_path("image/output1.png"));
     $process5 = new Process('magick  convert '.$path.'\image\output1.png  
     -flatten  '.$path.'\image\out.png 
     ');
      $process5->run();
         if (!$process5->isSuccessful()) {
          throw new ProcessFailedException($process5);
         }
          echo $process5->getOutput();
          echo '<img src="\image\out.png">'; 
  }



  public function uploadMockup51(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
   // dd($extension);
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $path = public_path();

    /*  $process0 = new Process(' convert -version
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 
      echo $process0->getOutput();

      dd(); */
    /*  $process0 = new Process('magick convert '.$path.'\site-images\Neseser.jpg
     -resize 1200x2000
     '.$path.'\site-images\Neseser.jpg
      ');
      $process0->run();
      if (!$process0->isSuccessful()) {
          throw new ProcessFailedException($process0);
      } 

      $processa = new Process('magick convert '.$path.'\site-images\PapirnakesaDinaHocrn.jpg
      -resize 1200x2000
      '.$path.'\site-images\PapirnakesaDinaHocrn.jpg
       ');
       $processa->run();
       if (!$processa->isSuccessful()) {
           throw new ProcessFailedException($processa);
       }  */

      $process = new Process('magick convert   '.$path.'\site-images\Neseser.jpg[303x322+630+481] 
     -colorspace gray 
     -blur 10x250 
     -auto-level
     '.$path.'\image\displace_map.png
      ');

 $process->run();
 if (!$process->isSuccessful()) {
     throw new ProcessFailedException($process);
 }
     echo $process->getOutput();
     echo '<img src="\image\displace_map.png">'; 

     $imageName1 = "/" .  $image; 

     $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
     -resize 200x200
     '.$path.'\design' . $imageName1 . '
     '); 
     
  $process1->run();
   if (!$process1->isSuccessful()) {
       throw new ProcessFailedException($process1);    
 } 


     $process2 = new Process('magick convert 
     '.$path.'\design' . $imageName1 . '
     -bordercolor transparent -border 12x12 -thumbnail 303x322 
     '.$path.'\image\ms_temp.png
      ');

 $process2->run();
 if (!$process1->isSuccessful()) {
     throw new ProcessFailedException($process2);
 }
     echo $process2->getOutput();
     echo '<img src="\image\ms_temp.png">';


     list($width, $height) = getimagesize($path.'\image\ms_temp.png');

    
     $X = 630 + (303-$width)/2;
     $Y = 481 +  (322-$height)/2;
    

      $process3 = new Process('magick convert 
     '.$path.'\site-images\Neseser.jpg[303x322+630+481] 
     -colorspace gray -blur 10x250 -auto-level 
     -depth 16 
     '.$path.'\image\ms_displace_map_girl_white_regular.png
      ');

 $process3->run();
 if (!$process3->isSuccessful()) {
     throw new ProcessFailedException($process3);
 }
     echo $process3->getOutput();
     echo '<img src="\image\ms_displace_map_girl_white_regular.png">'; 
    
     $process4 = new Process('magick convert ^
     '.$path.'\image\ms_temp.png ^
     '.$path.'\image\ms_displace_map_girl_white_regular.png ^
     -alpha set -virtual-pixel transparent ^
     -compose displace -set option:compose:args -5x-5 -composite ^
     -depth 16 ^
     '.$path.'\image\ms_displaced_logo.png
   
      ');

 $process4->run();
 if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
 }
     echo $process4->getOutput();
     echo '<img src="\image\ms_displaced_logo.png">';

     $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     -matte                     ^
     -virtual-pixel transparent ^
     -distort Perspective       ^
     "0,0,0,0 200,0,220,30 0,200,30,200 200,200,220,180" ^
     '.$path.'\image\ms_displaced_logo_perspective.png
   
      ');

 $process7->run();
 if (!$process7->isSuccessful()) {
     throw new ProcessFailedException($process7);
 }
     echo $process7->getOutput();
     echo '<img src="\image\ms_displaced_logo_perspective.png">';

     
      $process5 = new Process('magick convert ^
     '.$path.'\site-images\Neseser.jpg[303x322+630+481] ^
     -colorspace gray -auto-level ^
     -blur 0x4 ^
     -contrast-stretch 0,30%% ^
     -depth 16 ^
     '.$path.'\image\ms_light_map_girl_white_regular.png
      ');

/*         Makao sam komandu -separate proces 5 */

 $process5->run();
 if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
 }
     echo $process5->getOutput();
     echo '<img src="\image\ms_light_map_girl_white_regular.png">'; 
     
     $process6 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo_perspective.png ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask.png
      ');

 $process6->run();
 if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
 }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask.png">';
     
/*      $process7 = new Process('magick convert ^
     '.$path.'\image\ms_displaced_logo.png ^
     '.$path.'\image\ms_light_map_girl_white_regular.png ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo.png
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}
    echo $process7->getOutput();
    echo '<img src="\image\ms_light_map_logo.png">'; */
    
   
   /*  -geometry +984+1101 */

     $process8 = new Process('magick convert ^
     '.$path.'\site-images\Neseser.jpg ^
     '.$path.'\image\ms_displaced_logo_perspective.png ^
     -geometry +'.$X.'+'.$Y.'
     -compose over    -composite ^
     -depth 24 ^
     '.$path.'\image\ms_product.png
     ');

$process8->run();
if (!$process8->isSuccessful()) {
    throw new ProcessFailedException($process8);
}
    echo $process8->getOutput();
    echo '<img src="\image\ms_product.png">';
dd();

    $process9 = new Process('magick convert ^
    '.$path.'\site-images\PapirnakesaDinaHocrn.jpg ^
    '.$path.'\image\ms_displaced_logo.png ^
    -geometry +'.$X.'+'.$Y.'
    -compose over -composite ^
    -depth 16 ^
    '.$path.'\image\ms_product-crna.png
    ');

$process9->run();
if (!$process9->isSuccessful()) {
   throw new ProcessFailedException($process9);
}
   echo $process9->getOutput();
   echo '<img src="\image\ms_product-crna.png">';
  }



  public function uploadMockup52(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
   // dd($extension);
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 
    $path = public_path();


     $process = new Process('magick convert   '.$path.'\site-images\maskabijela.jpg[403x422+130+501] 
    
    '.$path.'\image\displace_map.png
     ');

$process->run();
if (!$process->isSuccessful()) {
    throw new ProcessFailedException($process);
}
    echo $process->getOutput();
    echo '<img src="\image\displace_map.png">'; 

    $imageName1 = "/" .  $image; 

    $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
    -resize 200x200
    '.$path.'\design' . $imageName1 . '
    '); 
    
 $process1->run();
  if (!$process1->isSuccessful()) {
      throw new ProcessFailedException($process1);    
} 


    $process2 = new Process('magick convert 
    '.$path.'\design' . $imageName1 . '
    -bordercolor transparent -border 12x12 -thumbnail 303x322 
    '.$path.'\image\ms_temp.png
     ');

$process2->run();
if (!$process1->isSuccessful()) {
    throw new ProcessFailedException($process2);
}
    echo $process2->getOutput();
    echo '<img src="\image\ms_temp.png">';


    list($width, $height) = getimagesize($path.'\image\ms_temp.png');

  
    $X = 130 + (303-$width)/2;
    $Y = 501 +  (322-$height)/2;
   

     $process3 = new Process('magick convert 
    '.$path.'\site-images\maskabijela.jpg[303x322+130+501] 
    -colorspace gray -blur 10x250 -auto-level 
    -depth 16 
    '.$path.'\image\ms_displace_map_girl_white_regular.png
     ');

$process3->run();
if (!$process3->isSuccessful()) {
    throw new ProcessFailedException($process3);
}
    echo $process3->getOutput();
    echo '<img src="\image\ms_displace_map_girl_white_regular.png">'; 
   
    $process4 = new Process('magick convert ^
    '.$path.'\image\ms_temp.png ^
    '.$path.'\image\ms_displace_map_girl_white_regular.png ^
    -alpha set -virtual-pixel transparent ^
    -compose displace -set option:compose:args -5x-5 -composite ^
    -depth 16 ^
    '.$path.'\image\ms_displaced_logo.png
  
     ');

$process4->run();
if (!$process4->isSuccessful()) {
    throw new ProcessFailedException($process4);
}
    echo $process4->getOutput();
    echo '<img src="\image\ms_displaced_logo.png">';

    $process7 = new Process('magick convert ^
    '.$path.'\image\ms_displaced_logo.png ^
    -matte                     ^
    -virtual-pixel transparent ^
    -distort Perspective       ^
    "0,0,0,0 200,0,220,30 0,200,30,250 200,200,220,170" ^
    '.$path.'\image\ms_displaced_logo_perspective.png
  
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}

    
     $process5 = new Process('magick convert ^
    '.$path.'\site-images\maskabijela.jpg[303x322+130+501] ^
    -colorspace gray -auto-level ^
    -blur 0x4 ^
    -contrast-stretch 0,30%% ^
    -depth 16 ^
    '.$path.'\image\ms_light_map_girl_white_regular.png
     ');

/*         Makao sam komandu -separate proces 5 */

$process5->run();
if (!$process5->isSuccessful()) {
    throw new ProcessFailedException($process5);
}
    echo $process5->getOutput();
    echo '<img src="\image\ms_light_map_girl_white_regular.png">'; 
    
    $process6 = new Process('magick convert ^
    '.$path.'\image\ms_displaced_logo_perspective.png ^
    -channel matte -separate ^
    '.$path.'\image\ms_logo_displace_mask.png
     ');

$process6->run();
if (!$process6->isSuccessful()) {
    throw new ProcessFailedException($process6);
}
    echo $process6->getOutput();
    echo '<img src="\image\ms_logo_displace_mask.png">';
    
    $process7 = new Process('magick convert ^
    '.$path.'\image\ms_displaced_logo_perspective.png ^
    '.$path.'\image\ms_light_map_girl_white_regular.png ^
    -compose Multiply -composite ^
    '.$path.'\image\ms_logo_displace_mask.png ^
    -compose CopyOpacity -composite ^
    '.$path.'\image\ms_light_map_logo.png
    ');

$process7->run();
if (!$process7->isSuccessful()) {
   throw new ProcessFailedException($process7);
}
   echo $process7->getOutput();
   echo '<img src="\image\ms_light_map_logo.png">';
   
  
  /*  -geometry +984+1101 */

    $process8 = new Process('magick convert ^
    '.$path.'\site-images\maskabijela.jpg ^
    '.$path.'\image\ms_light_map_logo.png ^
    -geometry +'.$X.'+'.$Y.'
    -compose over    -composite ^
    -depth 24 ^
    '.$path.'\image\ms_product.png
    ');

$process8->run();
if (!$process8->isSuccessful()) {
   throw new ProcessFailedException($process8);
}
   echo $process8->getOutput();
   echo '<img src="\image\ms_product.png">';


   $process9 = new Process('magick convert ^
   '.$path.'\site-images\maskacrna.jpg ^
   '.$path.'\image\ms_light_map_logo.png ^
   -geometry +550+630
   -compose over -composite ^
   -depth 16 ^
   '.$path.'\image\ms_product-crna.png
   ');

$process9->run();
if (!$process9->isSuccessful()) {
  throw new ProcessFailedException($process9);
}
  echo $process9->getOutput();
  echo '<img src="\image\ms_product-crna.png">';
  }

  public function uploadMockup53(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $imageName1 = "/" .  $image; 

     $path = public_path();


     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(500, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $src2 = new \Imagick(public_path("\site-images\Mug-Mask.png"));
   /*   $src2->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src2->writeImage(public_path("\site-images\Solja.jpg")); */
     
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
     
     $process5 = new Process('magick convert ^
     '.$path.'\site-images\Solja.png ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-canvas.png
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
     echo '<img src="\image\ms_light_map-canvas.png">';
     
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask_canvas.png
     ');
     
     
     
     $process6->run();
     if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
     }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask_canvas.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-canvas.png ^
     -geometry -300-250 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask_canvas.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo_canvas.png
     ');
     
     $process7->run();
     if (!$process7->isSuccessful()) {
     throw new ProcessFailedException($process7);
     }
     echo $process7->getOutput();
     echo '<img src="\image\ms_light_map_logo_canvas.png">';

     
     /* $src2->compositeImage($src1, \Imagick::COMPOSITE_DSTOVER ,390,450);
     $src2->writeImage(public_path("image\image-canvas.png")); */

     $src2->compositeImage($src1, \Imagick::COMPOSITE_DSTOVER  ,300,250);
     $src2->writeImage(public_path("image\image-canvas.png"));
     echo '<img src="\image\image-canvas.png">';
     $src4 = new \Imagick(public_path("site-images/Solja.png"));
  /*    $src4->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src4->writeImage(public_path("\site-images/Textura-Canvas-mockup.png")); */
      $src3 = new \Imagick(public_path("image/image-canvas.png"));
      $src3->compositeImage($src4, \Imagick::COMPOSITE_MULTIPLY    ,0,0);
      $src3->writeImage(public_path("image\multiply_canvas.png"));

    echo '<img src="\image\multiply_canvas.png">'; 
  }

  public function uploadMockup54(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $imageName1 = "/" .  $image; 

     $path = public_path();


     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(370, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $src2 = new \Imagick(public_path("\site-images\Solja2.png"));
   /*   $src2->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src2->writeImage(public_path("\site-images\Solja.jpg")); */
     
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
     
     $process5 = new Process('magick convert ^
     '.$path.'\site-images\Solja2.png ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-canvas.png
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
     echo '<img src="\image\ms_light_map-canvas.png">';
     
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask_canvas.png
     ');
     
     
     
     $process6->run();
     if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
     }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask_canvas.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-canvas.png ^
     -geometry -380-400 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask_canvas.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo_canvas.png
     ');
     
     $process7->run();
     if (!$process7->isSuccessful()) {
     throw new ProcessFailedException($process7);
     }
     echo $process7->getOutput();
     echo '<img src="\image\ms_light_map_logo_canvas.png">';


     $src2->compositeImage($src1, \Imagick::COMPOSITE_DEFAULT  ,380,400);
     $src2->writeImage(public_path("image\image-canvas.png"));
     echo '<img src="\image\image-canvas.png">';
     $src4 = new \Imagick(public_path("site-images/Solja2.png"));
  /*    $src4->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src4->writeImage(public_path("\site-images/Textura-Canvas-mockup.png")); */
      $src3 = new \Imagick(public_path("image/image-canvas.png"));
      $src3->compositeImage($src4, \Imagick::COMPOSITE_MULTIPLY,0,0);
      $src3->writeImage(public_path("image\multiply_canvas.png"));

    echo '<img src="\image\multiply_canvas.png">'; 
  }

  public function uploadMockup55(Request $request){
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

    
 
    

$src1 = new \Imagick(public_path("design". $imageName1));
$src1->resizeImage(400, null,\Imagick::FILTER_LANCZOS,1); 
$src1->writeImage(public_path("design". $imageName1));
$src2 = new \Imagick(public_path("\site-images\TermosThumbnail.png"));
/* $src3 = new \Imagick(public_path("\image\Iphone-II-Pro.jpg")); */


$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 

$process5 = new Process('magick convert ^
'.$path.'\site-images\TermosThumbnail.png ^
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
-geometry -700-420 ^
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
$src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 700, 420);
$src2->writeImage(public_path("image/output1.png"));
/* $process5 = new Process('magick  convert '.$path.'\image\output1.png -background "rgb(0,0,0)" 
-flatten  '.$path.'\image\out.png 
');
 $process5->run();
    if (!$process5->isSuccessful()) {
     throw new ProcessFailedException($process5);
    }
     echo $process5->getOutput(); */
     echo '<img src="\image\output1.png">'; 
  }


  public function uploadMockup56(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
   // dd($extension);
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 
    $path = public_path();
    $process0 = new Process('magick convert   '.$path.'\site-images\maskemockup-01.jpg
    -resize 1200x1200
    '.$path.'\site-images\maskemockup-01.jpg
     ');

$process0->run();
if (!$process0->isSuccessful()) {
    throw new ProcessFailedException($process);
}
     $process = new Process('magick convert   '.$path.'\site-images\maskemockup-01.jpg[403x422+150+201] 
    
    '.$path.'\image\displace_map.png
     ');

$process->run();
if (!$process->isSuccessful()) {
    throw new ProcessFailedException($process);
}
    echo $process->getOutput();
    echo '<img src="\image\displace_map.png">'; 

    $imageName1 = "/" .  $image; 

    $process1 = new Process('magick convert   '.$path.'\design' . $imageName1 . '
    -resize 200x200
    '.$path.'\design' . $imageName1 . '
    '); 
    
 $process1->run();
  if (!$process1->isSuccessful()) {
      throw new ProcessFailedException($process1);    
} 


    $process2 = new Process('magick convert 
    '.$path.'\design' . $imageName1 . '
    -bordercolor transparent -border 12x12 -thumbnail 303x322 
    '.$path.'\image\ms_temp.png
     ');

$process2->run();
if (!$process1->isSuccessful()) {
    throw new ProcessFailedException($process2);
}
    echo $process2->getOutput();
    echo '<img src="\image\ms_temp.png">';


    list($width, $height) = getimagesize($path.'\image\ms_temp.png');

  
    $X = 150 + (303-$width)/2;
    $Y = 201 +  (322-$height)/2;
   

     $process3 = new Process('magick convert 
    '.$path.'\site-images\maskemockup-01.jpg[303x322+150+201] 
    -colorspace gray -blur 10x250 -auto-level 
    -depth 16 
    '.$path.'\image\ms_displace_map_girl_white_regular.png
     ');

$process3->run();
if (!$process3->isSuccessful()) {
    throw new ProcessFailedException($process3);
}
    echo $process3->getOutput();
    echo '<img src="\image\ms_displace_map_girl_white_regular.png">'; 
   
    $process4 = new Process('magick convert ^
    '.$path.'\image\ms_temp.png ^
    '.$path.'\image\ms_displace_map_girl_white_regular.png ^
    -alpha set -virtual-pixel transparent ^
    -compose displace -set option:compose:args -5x-5 -composite ^
    -depth 16 ^
    '.$path.'\image\ms_displaced_logo.png
  
     ');

$process4->run();
if (!$process4->isSuccessful()) {
    throw new ProcessFailedException($process4);
}
    echo $process4->getOutput();
    echo '<img src="\image\ms_displaced_logo.png">';

    $process7 = new Process('magick convert ^
    '.$path.'\image\ms_displaced_logo.png ^
    -matte                     ^
    -virtual-pixel transparent ^
    -distort Perspective       ^
    "0,0,20,20 0,200,20,220 200,200,180,220 200,0,220,10" ^
    '.$path.'\image\ms_displaced_logo_perspective.png
  
     ');

$process7->run();
if (!$process7->isSuccessful()) {
    throw new ProcessFailedException($process7);
}

    
     $process5 = new Process('magick convert ^
    '.$path.'\site-images\maskemockup-01.jpg[303x322+150+201] ^
    -colorspace gray -auto-level ^
    -blur 0x4 ^
    -contrast-stretch 0,30%% ^
    -depth 16 ^
    '.$path.'\image\ms_light_map_girl_white_regular.png
     ');

/*         Makao sam komandu -separate proces 5 */

$process5->run();
if (!$process5->isSuccessful()) {
    throw new ProcessFailedException($process5);
}
    echo $process5->getOutput();
    echo '<img src="\image\ms_light_map_girl_white_regular.png">'; 
    
    $process6 = new Process('magick convert ^
    '.$path.'\image\ms_displaced_logo_perspective.png ^
    -channel matte -separate ^
    '.$path.'\image\ms_logo_displace_mask.png
     ');

$process6->run();
if (!$process6->isSuccessful()) {
    throw new ProcessFailedException($process6);
}
    echo $process6->getOutput();
    echo '<img src="\image\ms_logo_displace_mask.png">';
    
/*     $process7 = new Process('magick convert ^
    '.$path.'\image\ms_displaced_logo_perspective.png ^
    '.$path.'\image\ms_light_map_girl_white_regular.png ^
    -compose Multiply -composite ^
    '.$path.'\image\ms_logo_displace_mask.png ^
    -compose CopyOpacity -composite ^
    '.$path.'\image\ms_light_map_logo.png
    ');

$process7->run();
if (!$process7->isSuccessful()) {
   throw new ProcessFailedException($process7);
}
   echo $process7->getOutput();
   echo '<img src="\image\ms_light_map_logo.png">'; */
   
  
  /*  -geometry +984+1101 */

    $process8 = new Process('magick convert ^
    '.$path.'\site-images\maskemockup-01.jpg ^
    '.$path.'\image\ms_displaced_logo_perspective.png ^
    -geometry +'.$X.'+'.$Y.'
    -compose over    -composite ^
    -depth 24 ^
    '.$path.'\image\ms_product.png
    ');

$process8->run();
if (!$process8->isSuccessful()) {
   throw new ProcessFailedException($process8);
}
   echo $process8->getOutput();
   echo '<img src="\image\ms_product.png">';

dd();
   $process9 = new Process('magick convert ^
   '.$path.'\site-images\maskacrna.jpg ^
   '.$path.'\image\ms_light_map_logo.png ^
   -geometry +550+630
   -compose over -composite ^
   -depth 16 ^
   '.$path.'\image\ms_product-crna.png
   ');

$process9->run();
if (!$process9->isSuccessful()) {
  throw new ProcessFailedException($process9);
}
  echo $process9->getOutput();
  echo '<img src="\image\ms_product-crna.png">';
  }


  public function uploadMockup58(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $imageName1 = "/" .  $image; 

     $path = public_path();


     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(600, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $src2 = new \Imagick(public_path("\site-images\U1-Canvas_Slika-Maska.png"));
    /*  $src2->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src2->writeImage(public_path("\site-images\Canvas-mockup-thumbnail.png")); */
     
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 
     
     $process5 = new Process('magick convert ^
     '.$path.'\site-images\Canvas-mockup-thumbnail.png ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-canvas-thumb.png
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
     echo '<img src="\image\ms_light_map-canvas-thumb.png">';
     
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask_canvas_thumb.png
     ');
     
     
     
     $process6->run();
     if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
     }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask_canvas_thumb.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-canvas-thumb.png ^
     -geometry -300-230 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask_canvas_thumb.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo_canvas_thumb.png
     ');
     
     $process7->run();
     if (!$process7->isSuccessful()) {
     throw new ProcessFailedException($process7);
     }
     echo $process7->getOutput();
     echo '<img src="\image\ms_light_map_logo_canvas_thumb.png">';


     $src2->compositeImage($src1, \Imagick::COMPOSITE_DSTOVER ,300,230);
     $src2->writeImage(public_path("image\image-canvas-thumb.png"));
     echo '<img src="\image\image-canvas-thumb.png">';
     $src4 = new \Imagick(public_path("site-images/U1-Canvas_Slika-BG.png"));
    /*  $src4->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src4->writeImage(public_path("\site-images/Textura-Canvas-mockup.png")); */
      $src3 = new \Imagick(public_path("image/image-canvas-thumb.png"));
      $src3->compositeImage($src4, \Imagick::COMPOSITE_MULTIPLY,0,0);
      $src3->writeImage(public_path("image\multiply_canvas-thumb.png"));

    echo '<img src="\image\multiply_canvas-thumb.png">'; 
     dd();
     $src1->setImageVirtualPixelMethod(\Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
     $src = new \Imagick(public_path("\image\ms_light_map_logo_canvas.png"));
     $src2->compositeImage($src, \Imagick::COMPOSITE_COLORIZE , 300, 300);
     $src2->writeImage(public_path("image/output1.png"));
     $process5 = new Process('magick  convert '.$path.'\image\output1.png 
     -flatten  '.$path.'\image\out_canvas.png 
     ');
      $process5->run();
         if (!$process5->isSuccessful()) {
          throw new ProcessFailedException($process5);
         }
          echo $process5->getOutput();
          echo '<img src="\image\out_canvas.png">'; 
  }

  public function uploadMockup59(Request $request){
    $file = $request->file('file');
    $imageName =  $file->getClientOriginalName();
    $imageName = preg_replace('/\s+/', '', $imageName);
    $filename = pathinfo($imageName, PATHINFO_FILENAME);
    
    $extension =  $request->file('file')->getClientOriginalExtension();
    $image = $filename . "_" . time() . ".".$extension;
     $file->move('design/', $image); 

     $imageName1 = "/" .  $image; 

     $path = public_path();


     $src1 = new \Imagick(public_path("design". $imageName1));
     $src1->resizeImage(400, null,\Imagick::FILTER_LANCZOS,1); 
     $src1->writeImage(public_path("design". $imageName1));
     $src2 = new \Imagick(public_path("\site-images\podmetac-okrugli-1.png"));
     $src5 = new \Imagick(public_path("\site-images\podmetac-okrugli-2.png"));
   //  $src2 = new \Imagick(public_path("\site-images\podmetac-okrugli-1.png"));
     /* $src2->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src2->writeImage(public_path("\site-images\Canvas-mockup-thumbnail.png")); */
     
     $src1->setImageArtifact('compose:args', "1,0,-0.5,0.5"); 

     $process4 = new Process('magick convert ^
     '.$path.'\site-images\podmetac-okrugli-2.png ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-coaster2.png
     ');
     
     /* Makao sam komandu -separate proces 5   -colorspace gray -auto-level ^
     -blur 0x3 ^
     -contrast-stretch 0,50%% ^
     -depth 16 ^  -negate  -channel A -blur 0x8*/
     
     $process4->run();
     if (!$process4->isSuccessful()) {
     throw new ProcessFailedException($process4);
     }
     echo $process4->getOutput();
     
     $process5 = new Process('magick convert ^
     '.$path.'\site-images\podmetac-okrugli-1.png ^
     -channel A -blur 0x8
     -compose hardlight
     '.$path.'\image\ms_light_map-coaster1.png
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
     echo '<img src="\image\ms_light_map-coaster1.png">';
     
     $process6 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     -channel matte -separate ^
     '.$path.'\image\ms_logo_displace_mask_coaster1.png
     ');
     
     
     
     $process6->run();
     if (!$process6->isSuccessful()) {
     throw new ProcessFailedException($process6);
     }
     echo $process6->getOutput();
     echo '<img src="\image\ms_logo_displace_mask_coaster1.png">';
     
     $process7 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-coaster1.png ^
     -geometry -390-450 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask_coaster1.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo_coaster1.png
     ');
     
     $process7->run();
     if (!$process7->isSuccessful()) {
     throw new ProcessFailedException($process7);
     }
     echo $process7->getOutput();
     echo '<img src="\image\ms_light_map_logo_coaster1.png">';



     $process8 = new Process('magick convert ^
     '.$path.'\design'. $imageName1. ' ^
     '.$path.'\image\ms_light_map-coaster2.png ^
     -geometry -420-350 ^
     -compose Multiply -composite ^
     '.$path.'\image\ms_logo_displace_mask_coaster1.png ^
     -compose CopyOpacity -composite ^
     '.$path.'\image\ms_light_map_logo_coaster2.png
     ');
     
     $process8->run();
     if (!$process8->isSuccessful()) {
     throw new ProcessFailedException($process8);
     }
     echo $process8->getOutput();
     echo '<img src="\image\ms_light_map_logo_coaster2.png">';


     $src2->compositeImage($src1, \Imagick::COMPOSITE_DSTOVER ,390,450);
     $src2->writeImage(public_path("image\image-coaster1.png"));
     echo '<img src="\image\image-coaster1.png">';
     $src5->compositeImage($src1, \Imagick::COMPOSITE_DSTOVER ,420,350);
     $src5->writeImage(public_path("image\image-coaster2.png"));
     echo '<img src="\image\image-coaster2.png">';
     $src4 = new \Imagick(public_path("site-images/podmetac-okrugli-4-BG.png"));
     $src6 = new \Imagick(public_path("image\image-coaster2.png"));
    /*  $src4->resizeImage(1500, 1500,\Imagick::FILTER_LANCZOS,1); 
     $src4->writeImage(public_path("\site-images/Textura-Canvas-mockup.png")); */
      $src3 = new \Imagick(public_path("image/image-coaster1.png"));
      $src3->compositeImage($src4, \Imagick::COMPOSITE_MULTIPLY,0,0);
      $src3->writeImage(public_path("image\multiply_coaster.png"));
      $final = new \Imagick(public_path("image/multiply_coaster.png"));
      $final->compositeImage($src6, \Imagick::COMPOSITE_BUMPMAP   ,0,0);
    echo '<img src="\image\multiply_coaster.png">'; 
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
   $src1->resizeImage(400, null,\Imagick::FILTER_LANCZOS,1); 
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
   -geometry -340-320 ^
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
  $src2->compositeImage($src, \Imagick::COMPOSITE_DSTOVER, 340, 320);
  $src2->writeImage(public_path("image/output1.png"));
   $process5 = new Process('magick  convert '.$path.'\image\output1.png -background "rgb(0,0,0)" 
   -flatten  '.$path.'\image\out.png 
  ');
     $process5->run();
        if (!$process5->isSuccessful()) {
         throw new ProcessFailedException($process5);
        }
         echo $process5->getOutput();
         echo '<img src="\image\out.png">'; 

         $process10 = new Process('magick  convert '.$path.'\image\Iphone-II-Pro-Bezpozadine1.png -background "rgb(0,0,0)" 
         -flatten  '.$path.'\image\Iphone-II-Pro-Crna.png 
        ');
           $process10->run();
              if (!$process10->isSuccessful()) {
               throw new ProcessFailedException($process10);
              }
               echo $process10->getOutput();
               echo '<img src="\image\Iphone-II-Pro-Crna.png">'; 

  echo '<img src="/image/output1.png" alt="" />'; 
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
